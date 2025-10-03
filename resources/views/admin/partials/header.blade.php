<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Menu -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

      <div class="app-brand demo my-3">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
          <span class="app-brand-logo demo me-1">
            <span style="color:var(--bs-primary);">
              <svg width="30" height="24" viewBox="0 0 250 196" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.3002 1.25469L56.655 28.6432C59.0349 30.1128 60.4839 32.711 60.4839 35.5089V160.63C60.4839 163.468 58.9941 166.097 56.5603 167.553L12.2055 194.107C8.3836 196.395 3.43136 195.15 1.14435 191.327C0.395485 190.075 0 188.643 0 187.184V8.12039C0 3.66447 3.61061 0.0522461 8.06452 0.0522461C9.56056 0.0522461 11.0271 0.468577 12.3002 1.25469Z" fill="currentColor" />
                <path opacity="0.077704" fill-rule="evenodd" clip-rule="evenodd" d="M0 65.2656L60.4839 99.9629V133.979L0 65.2656Z" fill="black" />
                <path opacity="0.077704" fill-rule="evenodd" clip-rule="evenodd" d="M0 65.2656L60.4839 99.0795V119.859L0 65.2656Z" fill="black" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M237.71 1.22393L193.355 28.5207C190.97 29.9889 189.516 32.5905 189.516 35.3927V160.631C189.516 163.469 191.006 166.098 193.44 167.555L237.794 194.108C241.616 196.396 246.569 195.151 248.856 191.328C249.605 190.076 250 188.644 250 187.185V8.09597C250 3.64006 246.389 0.027832 241.935 0.027832C240.444 0.027832 238.981 0.441882 237.71 1.22393Z" fill="currentColor" />
                <path opacity="0.077704" fill-rule="evenodd" clip-rule="evenodd" d="M250 65.2656L189.516 99.8897V135.006L250 65.2656Z" fill="black" />
                <path opacity="0.077704" fill-rule="evenodd" clip-rule="evenodd" d="M250 65.2656L189.516 99.0497V120.886L250 65.2656Z" fill="black" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.2787 1.18923L125 70.3075V136.87L0 65.2465V8.06814C0 3.61223 3.61061 0 8.06452 0C9.552 0 11.0105 0.411583 12.2787 1.18923Z" fill="currentColor" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.2787 1.18923L125 70.3075V136.87L0 65.2465V8.06814C0 3.61223 3.61061 0 8.06452 0C9.552 0 11.0105 0.411583 12.2787 1.18923Z" fill="white" fill-opacity="0.15" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M237.721 1.18923L125 70.3075V136.87L250 65.2465V8.06814C250 3.61223 246.389 0 241.935 0C240.448 0 238.99 0.411583 237.721 1.18923Z" fill="currentColor" />
                <path fill-rule="evenodd" clip-rule="evenodd" d="M237.721 1.18923L125 70.3075V136.87L250 65.2465V8.06814C250 3.61223 246.389 0 241.935 0C240.448 0 238.99 0.411583 237.721 1.18923Z" fill="white" fill-opacity="0.3" />
              </svg>
            </span>
          </span>
          <span class="app-brand-text demo menu-text fw-semibold ms-2">E-Commerce</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
          <i class="menu-toggle-icon d-xl-block align-middle"></i>
        </a>
      </div>

      <div class="menu-inner-shadow"></div>

      <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <a href="{{ route('admin.dashboard') }}" class="menu-link">
            <i class="menu-icon tf-icons ri-home-smile-line"></i>
            <div data-i18n="Dashboard">Dashboard</div>
          </a>
        </li>

        <!-- E-Commerce Management -->
        <li class="menu-header mt-7">
          <span class="menu-header-text" data-i18n="E-Commerce Management">E-Commerce Management</span>
        </li>
        
        <!-- Products -->
        <li class="menu-item {{ request()->routeIs('admin.products.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-shopping-bag-3-line'></i>
            <div data-i18n="Products">Products</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
              <a href="{{ route('admin.products.index') }}" class="menu-link">
                <div data-i18n="Product List">Product List</div>
              </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
              <a href="{{ route('admin.products.create') }}" class="menu-link">
                <div data-i18n="Add Product">Add Product</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Categories -->
        <li class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-list-check'></i>
            <div data-i18n="Categories">Categories</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
              <a href="{{ route('admin.categories.index') }}" class="menu-link">
                <div data-i18n="Category List">Category List</div>
              </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.categories.create') ? 'active' : '' }}">
              <a href="{{ route('admin.categories.create') }}" class="menu-link">
                <div data-i18n="Add Category">Add Category</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Attributes -->
        <li class="menu-item {{ request()->routeIs('admin.attributes.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-settings-3-line'></i>
            <div data-i18n="Attributes">Attributes</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.attributes.index') ? 'active' : '' }}">
              <a href="{{ route('admin.attributes.index') }}" class="menu-link">
                <div data-i18n="Attribute List">Attribute List</div>
              </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.attributes.create') ? 'active' : '' }}">
              <a href="{{ route('admin.attributes.create') }}" class="menu-link">
                <div data-i18n="Add Attribute">Add Attribute</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Orders -->
        <li class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-file-list-3-line'></i>
            <div data-i18n="Orders">Orders</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
              <a href="{{ route('admin.orders.index') }}" class="menu-link">
                <div data-i18n="Order List">Order List</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Customers -->
        <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-user-line'></i>
            <div data-i18n="Customers">Customers</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
              <a href="{{ route('admin.users.index') }}" class="menu-link">
                <div data-i18n="Customer List">Customer List</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Reviews -->
        <li class="menu-item {{ request()->routeIs('admin.reviews.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-star-line'></i>
            <div data-i18n="Reviews">Reviews</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.reviews.index') ? 'active' : '' }}">
              <a href="{{ route('admin.reviews.index') }}" class="menu-link">
                <div data-i18n="Review List">Review List</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Coupons -->
        <li class="menu-item {{ request()->routeIs('admin.coupons.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-coupon-line'></i>
            <div data-i18n="Coupons">Coupons</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.coupons.index') ? 'active' : '' }}">
              <a href="{{ route('admin.coupons.index') }}" class="menu-link">
                <div data-i18n="Coupon List">Coupon List</div>
              </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.coupons.create') ? 'active' : '' }}">
              <a href="{{ route('admin.coupons.create') }}" class="menu-link">
                <div data-i18n="Add Coupon">Add Coupon</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Media Library -->
        <li class="menu-item {{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
          <a href="{{ route('admin.media.index') }}" class="menu-link">
            <i class='menu-icon tf-icons ri-image-line'></i>
            <div data-i18n="Media Library">Media Library</div>
          </a>
        </li>

        <!-- Blog Management -->
        <li class="menu-header mt-7">
          <span class="menu-header-text" data-i18n="Blog Management">Blog Management</span>
        </li>

        <!-- Blog Posts -->
        <li class="menu-item {{ request()->routeIs('admin.blogs.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-file-text-line'></i>
            <div data-i18n="Blog Posts">Blog Posts</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.blogs.index') ? 'active' : '' }}">
              <a href="{{ route('admin.blogs.index') }}" class="menu-link">
                <div data-i18n="Post List">Post List</div>
              </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.blogs.create') ? 'active' : '' }}">
              <a href="{{ route('admin.blogs.create') }}" class="menu-link">
                <div data-i18n="Add Post">Add Post</div>
              </a>
            </li>
          </ul>
        </li>

        <!-- Blog Categories -->
        <li class="menu-item {{ request()->routeIs('admin.blog-categories.*') ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class='menu-icon tf-icons ri-folder-line'></i>
            <div data-i18n="Blog Categories">Blog Categories</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item {{ request()->routeIs('admin.blog-categories.index') ? 'active' : '' }}">
              <a href="{{ route('admin.blog-categories.index') }}" class="menu-link">
                <div data-i18n="Category List">Category List</div>
              </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.blog-categories.create') ? 'active' : '' }}">
              <a href="{{ route('admin.blog-categories.create') }}" class="menu-link">
                <div data-i18n="Add Category">Add Category</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>

    </aside>
    <!-- / Menu -->

    <!-- Layout container -->
    <div class="layout-page">
      <!-- Navbar -->
      @include('admin.partials.top_navbar')