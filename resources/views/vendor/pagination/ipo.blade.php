@php
  $p = $paginator;
@endphp
@if($p->hasPages())
<nav class="pager no-print" aria-label="Navigasi halaman">
  @if($p->onFirstPage())
    <span class="pager-btn is-disabled">‹ Prev</span>
  @else
    <a class="pager-btn" href="{{ $p->previousPageUrl() }}" rel="prev">‹ Prev</a>
  @endif
  <span class="pager-info">Hal {{ $p->currentPage() }} / {{ $p->lastPage() }}</span>
  @if($p->hasMorePages())
    <a class="pager-btn" href="{{ $p->nextPageUrl() }}" rel="next">Next ›</a>
  @else
    <span class="pager-btn is-disabled">Next ›</span>
  @endif
</nav>
@endif
