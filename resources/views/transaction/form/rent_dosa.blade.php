<style>
    .dosa-entries .toggler .bi-chevron-up{
        display:none;
    }
    .dosa-entries.expanded .toggler .bi-chevron-up, .dosa-entries .toggler .bi-chevron-down{
        display:inline-block;
    }
    .dosa-entries.expanded .toggler .bi-chevron-down{
        display:none;
    }
    .dosa-entries .toggled-data{
        display:none;
    }
    .dosa-entries.expanded .toggled-data{
        display:table-row;
    }
</style>
<div class="row">
    <div class="col-12">
        <td>
            <div class="row mx-0 my-2">
                <div class="col-auto"><h5>History Dosa</h5></div>
                <div class="col-auto"><a href="{{route('transaction.rent.dosa.view', ['transaction_id' => $transaction_id])}}" class="btn btn-sm btn-outline-primary">See More</a></div>
            </div>
        </td>
    </div>
    <div class="col-12">
        <div id="dosa_container" class="card-container">
            
        </div>
    </div>
</div>
@push('js_stack')
<div class="modal fade" id="dosa_lampiran_modal" tabindex="-1" aria-labelledby="dosa_lampiran_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="dosa_lampiran_modal_label">Lampiran Dosa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="lampiran_dosa_image" class="w-100" src="">
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        search_dosa();
    });

    $(document).on('click', '.dosa-entries .toggler', function(){
        var row = $(this);
        var entry = row.closest('.dosa-entries');
        entry.toggleClass('expanded');
    });

    function search_dosa(){
        var search = "";
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.dosa.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'search':search,
                'transaction_id':"{{$transaction_id}}"
            },
            success : function(msg) {
                var rs = msg;

                show_dosa_data(rs["data"]);
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }

    function show_dosa_data(data){
        var cards = '';

        $.each(data, function(a,b){
            var tanggal_jam = "";
            tanggal_jam = moment(b.header_datetime).format("DD MMM YYYY hh:mm");

            var table_dosa = "";
            $.each(b.details, function(c,d){
                var btn_lampiran = "";
                if(d.image_path){
                    btn_lampiran = `<button type="button" class="btn btn-sm btn-primary" onclick="show_lampiran('`+d.image_path+d.image_name+`')">Lampiran</button>`
                }
                table_dosa += `
                    <tr>
                        <td>`+(c+1)+`.</td>
                        <td>`+d.product_name+`</td>
                        <td>`+d.product_brand_name+`</td>
                        <td>`+d.item_code+` - `+d.owner_name+`</td>
                        <td>`+d.condition_name+`</td>
                        <td>`+d.dosa_reason+`</td>
                        <td>
                            `+btn_lampiran+`
                        </td>
                    </tr>
                `;
            });

            var table
            cards += `
                <div class="card mb-2">
                    <div class="card-body">
                        <table class="table w-100 dosa-entries">
                            <tr class="toggler">
                                <td class="auto-width"><i class="bi bi-chevron-down me-2"></i><i class="bi bi-chevron-up me-2"></i>Tanggal & Jam Lapor</td>
                                <td>`+tanggal_jam+`</td>
                            </tr>
                            <tr class="toggled-data">
                                <td>Keterangan</td>
                                <td>`+b.header_notes+`</td>
                            </tr>
                            <tr class="toggled-data">
                                <td colspan="2">List Alat Bermasalah:</td>
                            </tr>
                            <tr class="toggled-data">
                                <td colspan="2">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Nama Barang</th>
                                                <th>Brand</th>
                                                <th>Inventory</th>
                                                <th>Dosa</th>
                                                <th>Keterangan</th>
                                                <th>Lampiran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            `+table_dosa+`
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
        });

        if(cards == ''){
            cards = `
                <div class="card">
                    <div class="card-body">Belum ada data</div>
                </div>
            `
        }

        $("#dosa_container").html(cards);
    }

    function show_lampiran(url){
        $("#lampiran_dosa_image").attr("src", "{{asset('/')}}"+url);
        $("#dosa_lampiran_modal").modal('show');
    }
</script>
@endpush