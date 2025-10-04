<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data ini?</p> 
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST" class="w-100 mx-auto">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-between w-100 mx-auto">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>                
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ketika tombol hapus diklik
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                let action = this.getAttribute('data-action'); // ambil route dari tombol
                let form = document.getElementById('deleteForm');
                form.setAttribute('action', action);
                $('#deleteModal').modal('show'); // tampilkan modal
            });
        });
    });
</script>
