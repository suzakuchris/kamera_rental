<div class="row">
    <div class="col-12">
        <td>
            <div class="row mx-0 my-2">
                <div class="col-auto"><h5>History Serah Terima</h5></div>
                <div class="col-auto"><a href="{{route('transaction.rent.serah_terima.view', ['transaction_id' => $transaction_id])}}" class="btn btn-sm btn-outline-primary">See More</a></div>
            </div>
        </td>
    </div>
    <div class="col-12">
        <div id="serah_terima_container" class="card-container">
            
        </div>
    </div>
</div>
@push('js_stack')
<div class="modal fade" id="rent_serah_terima_modal" tabindex="-1" aria-labelledby="rent_serah_terima_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="rent_serah_terima_modal_label">Lampiran Serah Terima</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rent_serah_terima_container_wrapper" class="carousel slide">
                    <div class="carousel-inner" id="rent_serah_terima_container">
                        
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#rent_serah_terima_container_wrapper" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#rent_serah_terima_container_wrapper" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        search_serah_terima();
    });

    function search_serah_terima(){
        var search = "";
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.serah_terima.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'search':search,
                'transaction_id':"{{$transaction_id}}"
            },
            success : function(msg) {
                var rs = msg;

                show_serah_terima_data(rs["data"]);
                // $("#serah_terima-table .pagination-links").html($(msg.pagination));
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }

    function show_serah_terima_data(data){
        var cards = '';

        $.each(data, function(a,b){
            var details = b.details;
            console.log(details);
            var table_serah = "";
            $.each(details, function(c,d){
                var bundle_suffix = '';
                if(d.bundle_name){
                    bundle_suffix = `(part of bundle '`+d.bundle_name+`')`;
                }
                table_serah += `
                    <tr>
                        <td>`+(c+1)+`.</td>
                        <td>`+d.product_name+` `+bundle_suffix+`</td>
                        <td>`+d.product_brand_name+`</td>
                        <td>`+d.item_code+` - `+d.owner_name+`</td>
                    </tr>
                `;
            });

            var tanggal_jam = "";
            tanggal_jam = moment(b.header_datetime).format("DD MMM YYYY hh:mm");
            cards += `
                <div class="card mb-2">
                    <div class="card-body">
                        <table class="table w-100">
                            <tr>
                                <td class="auto-width">Tanggal & Jam</td>
                                <td>`+tanggal_jam+`</td>
                            </tr>
                            <tr>
                                <td>Keterangan</td>
                                <td>`+b.header_notes+`</td>
                            </tr>
                            <tr>
                                <td>Lampiran</td>
                                <td><button type="button" onclick="show_lampiran_serah_terima('`+b.header_id+`')" class="btn btn-sm btn-primary">Lampiran</button></td>
                            </tr>
                            <tr>
                                <td>Tanda Terima Alat</td>
                                <td><a target="_blank" href="{{route('transaction.rent.serah_terima.print')}}/`+b.header_id+`" class="btn btn-sm btn-primary"><i class="bi bi-print me-2"></i>Cetak</a></td>
                            </tr>
                            <tr>
                                <td colspan="2">List Alat Serah Terima:</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Nama Barang</th>
                                                <th>Brand</th>
                                                <th>Inventory</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            `+table_serah+`
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

        $("#serah_terima_container").html(cards);
    }

    function show_lampiran_serah_terima(serah_terima_id){
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.serah_terima.attachment")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'serah_terima_id':serah_terima_id
            },
            success : function(msg) {
                var images_data = msg;
                var images = '';
                $.each(images_data, function(a,b){
                    var active = '';
                    if(a==0){
                        active = 'active';
                    }
                    images += `
                        <div class="carousel-item `+active+`">
                            <img src="`+b+`" class="d-block w-100">
                        </div>
                    `;
                });

                $("#rent_serah_terima_container").html(images);
                $("#rent_serah_terima_modal").modal('show');
                $('#rent_serah_terima_modal .carousel').carousel({
                    interval: 0
                });
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }
</script>
@endpush