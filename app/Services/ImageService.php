<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Limites máximos permitidos para imagens
     */
    public const MAX_WIDTH = 1920;
    public const MAX_HEIGHT = 1080;
    public const JPEG_QUALITY = 90;

    /**
     * Processa a imagem enviada:
     * - Corrige orientação EXIF se disponível
     * - Redimensiona proporcionalmente para até 1920x1080 (se for maior)
     * - Mantém o tamanho original se for menor ou igual ao limite
     * - Converte qualquer formato (png, webp, bmp, gif, etc.) para JPG com qualidade 90%
     * - Trata transparências com fundo branco
     * - Armazena no disco público e remove arquivos temporários
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string Caminho relativo no storage public (ex: pets/xyz.jpg)
     */
    public function processAndStore(UploadedFile $file, string $directory = 'pets'): string
    {
        $realPath = $file->getRealPath();

        // 1. Carregar recurso GD da imagem
        $sourceImage = $this->createImageFromFile($realPath);
        if (!$sourceImage) {
            throw new \InvalidArgumentException('Não foi possível processar o arquivo de imagem.');
        }

        // 2. Corrigir orientação EXIF (fotos tiradas de smartphones)
        $sourceImage = $this->fixOrientation($sourceImage, $realPath);

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // 3. Calcular dimensões proporcionais
        [$targetWidth, $targetHeight] = $this->calculateDimensions($origWidth, $origHeight);

        // 4. Criar imagem de destino true color
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Preencher o fundo de branco (evita fundo preto ao converter PNG/WEBP com transparência para JPG)
        $whiteBackground = imagecolorallocate($targetImage, 255, 255, 255);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $whiteBackground);

        // 5. Reamostrar imagem com interpolação de alta qualidade
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $origWidth,
            $origHeight
        );

        // 6. Gerar arquivo temporário JPG com qualidade 90%
        $tempPath = tempnam(sys_get_temp_dir(), 'pet_img_') . '.jpg';
        imagejpeg($targetImage, $tempPath, self::JPEG_QUALITY);

        // 7. Liberar recursos de memória GD
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        // 8. Salvar no disco local privado do Laravel
        $filename = Str::random(40) . '.jpg';
        $finalStoragePath = trim($directory, '/') . '/' . $filename;

        Storage::disk('local')->put($finalStoragePath, file_get_contents($tempPath));

        // 9. Deletar arquivo temporário
        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return $finalStoragePath;
    }

    /**
     * Calcula dimensões proporcionais respeitando o limite máximo sem deformar.
     */
    public function calculateDimensions(int $origWidth, int $origHeight): array
    {
        if ($origWidth <= self::MAX_WIDTH && $origHeight <= self::MAX_HEIGHT) {
            // Já está dentro dos limites, mantém resolução original
            return [$origWidth, $origHeight];
        }

        // Fator de escala proporcional: o menor entre largura e altura
        $scale = min(self::MAX_WIDTH / $origWidth, self::MAX_HEIGHT / $origHeight);

        $newWidth = (int) max(1, round($origWidth * $scale));
        $newHeight = (int) max(1, round($origHeight * $scale));

        return [$newWidth, $newHeight];
    }

    /**
     * Cria um recurso GD a partir de qualquer formato suportado.
     *
     * @return \GdImage|resource|false
     */
    protected function createImageFromFile(string $filePath)
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        return @imagecreatefromstring($content);
    }

    /**
     * Corrige orientação EXIF comum em fotos capturadas por smartphones.
     *
     * @param \GdImage|resource $image
     * @param string $filePath
     * @return \GdImage|resource
     */
    protected function fixOrientation($image, string $filePath)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($filePath);
        if (empty($exif['Orientation'])) {
            return $image;
        }

        switch ($exif['Orientation']) {
            case 3:
                $rotated = imagerotate($image, 180, 0);
                imagedestroy($image);
                return $rotated;
            case 6:
                $rotated = imagerotate($image, -90, 0);
                imagedestroy($image);
                return $rotated;
            case 8:
                $rotated = imagerotate($image, 90, 0);
                imagedestroy($image);
                return $rotated;
            default:
                return $image;
        }
    }
}
