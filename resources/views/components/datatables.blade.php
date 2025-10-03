<!-- resources/views/components/datatables.blade.php -->
@push('scripts')
<script>
    function initDataTable(tableId){
        $('#' + tableId).DataTable({
            "pageLength": 10,
            "lengthMenu": [ [10, 25, 50, 100], [10, 25, 50, 100] ],
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari total _MAX_ data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "›",
                    "previous": "‹"
                }
            }
        });
    }
</script>
@endpush
