<style>
    #payment-table tbody tr:not(.no-data){
        counter-increment: rowNumber;
    }

    #payment-table tbody tr:not(.no-data) td:first-child::before {
        display: table-cell;
        content: counter(rowNumber) ".";
        padding-right: 0.3em;
        text-align: right;
    }
</style>
<div class="row">
    <div class="col-12">
        <table id="payment-table" class="table">
            <thead>
                <tr>
                    <td colspan="8"><h5>History Payment</h5></td>
                </tr>
                <tr>
                    <th>No.</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Debit / Kredit</th>
                    <th>Tanggal Payment</th>
                    <th>Lampiran</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@push('js_stack')
<div class="modal fade" id="rent_payment_modal" tabindex="-1" aria-labelledby="rent_payment_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="rent_payment_modal_label">Lampiran Payment</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="rent_payment_container_wrapper" class="carousel slide">
                    <div class="carousel-inner" id="rent_payment_container">
                        
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#rent_payment_container_wrapper" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#rent_payment_container_wrapper" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var curr_page_payment = 1;
    var max_row_payment = 10;
    $(document).ready(function(){
        search_payment();
    });

    function search_payment(){
        var search = "";
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.payment.search")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'search':search,
                'transaction_id':"{{$transaction_id}}"
            },
            success : function(msg) {
                var rs = msg;

                show_payment_data(rs["data"]);
                // $("#payment-table .pagination-links").html($(msg.pagination));
            },
            error     : function(xhr) {
                console.log(xhr);
            },
            complete : function(xhr,status){
                closeLoading();
            }
        });
    }

    function show_payment_data(data){
        var rows = '';
        var curr_page_payment = curr_page_payment;
        var page = (curr_page_payment * max_row_payment) - max_row_payment;
        $.each(data, function(x,y){
            var amount = y.entry_debit;
            var tipe_flow = "Debit";
            if(y.entry_cashflow == 1){
                amount = y.entry_credit;
                tipe_flow = 'Kredit';
            }

            rows += `
                <tr>
                    <td></td>
                    <td>`+numberWithCommas(amount)+`</td>
                    <td>`+y.entry_notes+`</td>
                    <td>`+tipe_flow+`</td>
                    <td>`+moment(y.entry_date).format('DD MMM YYYY')+`</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" onclick="show_lampiran_payment(`+y.entry_id+`)">Lampiran</button>
                    </td>
                </tr>
            `;
        });

        if(rows == ''){
            var length = $("#payment-table thead th").length;
            rows = `
                <tr class="no-data">
                    <td colspan="`+length+`">Data kosong</td>
                </tr>
            `
        }

        $("#payment-table tbody").html(rows);
    }

    function show_lampiran_payment(payment_id){
        showLoading();
        $.ajax({
            type    : 'POST',
            url     : '{{route("transaction.rent.payment.attachment")}}',
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            dataType: 'JSON',
            data    : {
                'payment_id':payment_id
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

                $("#rent_payment_container").html(images);
                $("#rent_payment_modal").modal('show');
                $('#rent_payment_modal .carousel').carousel({
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