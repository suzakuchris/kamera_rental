<div class="row pagination-wrapper">
    <div class="col-auto">
        <select id="row_count" class="form-control mb-2">
            <option value="10">10 Rows</option>
            <option value="25">25 Rows</option>
            <option value="50">50 Rows</option>
            <option value="100">100 Rows</option>
        </select>
    </div>
    <div class="col d-flex align-items-center d-none">
        <div class="small"><span class="from-data"></span>-<span class="to-data"></span> out of <span class="total-data"></span> data</div>
    </div>
    <div class="col">
        <div class="pagination-links"></div>
    </div>
</div>
<script>
    $(document).on('click', "a.page-link", function(event){
        event.preventDefault();
        var url_string = $(this).attr('href');
        var url = new URL(url_string);
        var page = url.searchParams.get("page");
        curr_page = page;
        search_process();
    });
</script>