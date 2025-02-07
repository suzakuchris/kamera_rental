<nav class="sidebar p-relative">
    <div class="card h-100" style="border:0px;">
      <div class="card-header p-0 border-0" style="background:transparent;border:0px;">
        <header>
            <span class="image">
                <img class="w-100" src="{{asset(site_config()->site_logo)}}" alt="">
            </span>
            @if(false)<i class='bi bi-chevron-right toggle p-1' style="font-size:10px;"></i>@endif
        </header>
      </div>
      <div class="card-body px-0 h-100 overflow-auto">
        <div class="menu-bar">
            <div class="menu mt-0">
              @if(false)
              <li class="search-box">
                  <i class='bi bi-search icon'></i>
                  <input type="text" placeholder="Search...">
              </li>
              @endif
              <ul class="menu-links ps-0 my-0">
                  <li class="nav-link">
                    <a class="py-2" href="{{route('dashboard')}}">
                        <i class='bi bi-house icon'></i>
                        <span class="text nav-text">Dashboard</span>
                    </a>
                  </li>
                  <li class="nav-link row">
                    <div class="col-12">
                      <a class="py-2" href="#master" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                          <i class='bi bi-database icon'></i>
                          <span class="text nav-text">Master</span>
                      </a>
                    </div>
                    <div class="collapse ps-4" id="collapseExample">
                      <a class="py-2 my-1" href="{{route('master.product_brands')}}">
                          <i class='bi bi-camera icon'></i>  
                          <span class="text nav-text">Merek</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.product_types')}}">
                          <i class='bi bi-battery-full icon'></i>  
                          <span class="text nav-text">Kategori</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.product')}}">
                          <i class='bi bi-camera-reels-fill icon'></i>  
                          <span class="text nav-text">Produk</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.product_bundle')}}">
                          <i class='bi bi-inboxes-fill icon'></i>  
                          <span class="text nav-text">Paket Sewa</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.item')}}">
                          <i class='bi bi-clipboard-fill icon'></i>  
                          <span class="text nav-text">Inventory</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.users')}}">
                          <i class='bi bi-person-badge icon'></i>  
                          <span class="text nav-text">Akun</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.customer')}}">
                          <i class='bi bi-people-fill icon'></i>  
                          <span class="text nav-text">Customer</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.mitra')}}">
                          <i class='bi bi-file-earmark-fill icon'></i>  
                          <span class="text nav-text">Mitra</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.rekening')}}">
                          <i class='bi bi-piggy-bank icon'></i>  
                          <span class="text nav-text">Rekening</span>
                      </a>
                      @if(false)
                      <a class="py-2 my-1" href="{{route('master.item_status')}}">
                          <i class='bi bi-arrow-return-right icon'></i>  
                          <span class="text nav-text">Status</span>
                      </a>
                      <a class="py-2 my-1" href="{{route('master.item_condition')}}">
                          <i class='bi bi-arrow-return-right icon'></i>  
                          <span class="text nav-text">Condition</span>
                      </a>
                      @endif
                    </div>
                  </li>
                  <li class="nav-link row">
                    <div class="col-12">
                      <a class="py-2" href="#master" data-bs-toggle="collapse" data-bs-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample2">
                          <i class='bi bi-cart-fill icon'></i>
                          <span class="text nav-text">Operation</span>
                      </a>
                    </div>
                    <div class="collapse ps-4" id="collapseExample2">
                      <a class="py-2 my-1" href="{{route('transaction.rent')}}">
                          <i class='bi bi-cart-plus-fill icon'></i>  
                          <span class="text nav-text">Transaction</span>
                      </a>
                    </div>
                  </li>
                  <li class="nav-link">
                    <a class="py-2" href="{{route('config.main')}}">
                        <i class='bi bi-gear icon'></i>
                        <span class="text nav-text">Settings</span>
                    </a>
                  </li>
              </ul>
            </div>
        </div>
      </div>
      <div class="card-footer menu-bar1 px-0" style="background:transparent;border:0px;">
        <div class="bottom-content">
          <li class="">
              <a class="py-2" href="{{route('logout')}}">
              <i class='bi bi-power icon'></i>
                <span class="text nav-text">Logout</span>
              </a>
          </li>

          <li class="mode">
              <span class="mode-text text"><i class='bi bi-moon pe-2'></i>Dark mode</span>
              <div class="toggle-switch">
              <span class="switch"></span>
              </div>
          </li>
        </div>
      </div>
    </div>
</nav>

<script>
    const body = document.querySelector('body'),
    sidebar = body.querySelector('nav'),
    toggle = body.querySelector(".toggle"),
    searchBtn = body.querySelector(".search-box"),
    modeSwitch = body.querySelector(".toggle-switch"),
    modeText = body.querySelector(".mode-text");

    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
    })
    // searchBtn.addEventListener("click", () => {
    //     sidebar.classList.remove("close");
    // })
    modeSwitch.addEventListener("click", () => {
        body.classList.toggle("dark");
        if (body.classList.contains("dark")) {
          $(modeText).html("<i class='bi bi-moon pe-2'></i>Dark mode");
          $("html").attr("data-bs-theme", "dark");
        } else {
          $(modeText).html("<i class='bi bi-sun pe-2'></i>Light mode");
          $("html").removeAttr('data-bs-theme');
        }
    });
</script>