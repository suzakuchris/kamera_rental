<style>
    .loader {
        width: 15px;
        aspect-ratio: 1;
        border-radius: 50%;
        animation: l5 1s infinite linear alternate;
    }
    @keyframes l5 {
        0%  {box-shadow: 20px 0 #fff, -20px 0 #dfdfdfad;background: #fff }
        33% {box-shadow: 20px 0 #fff, -20px 0 #dfdfdfad;background: #dfdfdfad}
        66% {box-shadow: 20px 0 #dfdfdfad,-20px 0 #fff; background: #dfdfdfad}
        100%{box-shadow: 20px 0 #dfdfdfad,-20px 0 #fff; background: #fff }
    }
</style>
<div class="d-none loader-wrapper vw-100 vh-100 position-fixed d-flex justify-content-center align-items-center" style="top: 0;left: 0;background: #9595954f;z-index: 100000;">
    <div class="loader"></div>
</div>
<script>
    function showLoading(){
        $(".loader-wrapper").addClass('d-flex');
        $(".loader-wrapper").removeClass('d-none');
    }

    function closeLoading(){
        $(".loader-wrapper").removeClass('d-flex');
        $(".loader-wrapper").addClass('d-none');
    }
</script>
