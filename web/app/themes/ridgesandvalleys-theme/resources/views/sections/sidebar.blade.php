@if (is_active_sidebar('sidebar-primary'))
  <aside class="rv-sidebar" aria-label="{{ __('Sidebar', 'sage') }}">
    @php(dynamic_sidebar('sidebar-primary'))
  </aside>
@endif
