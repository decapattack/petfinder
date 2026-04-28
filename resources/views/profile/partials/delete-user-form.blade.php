<section>
    <p class="text-muted small mb-4">
        Após excluir sua conta, todos os dados serão permanentemente apagados.
        Baixe qualquer informação que deseje manter antes de continuar.
    </p>

    {{-- Botão que abre o modal Bootstrap --}}
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        {{ __('Excluir Conta') }}
    </button>

    {{-- Modal Bootstrap 5 --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">⚠️ Confirmar Exclusão de Conta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p class="text-muted small">
                            Esta ação é irreversível. Todos os seus pets, alertas e dados serão excluídos.
                            Digite sua senha para confirmar.
                        </p>

                        <div class="mb-3">
                            <x-input-label for="delete_password" :value="__('Senha')" />
                            <x-text-input id="delete_password" name="password" type="password"
                                          placeholder="{{ __('Confirme sua senha') }}" />
                            <x-input-error :messages="$errors->userDeletion->get('password')" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir Conta Permanentemente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
