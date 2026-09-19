@if ($paginator->hasPages())
  <nav class="pagination-a" role="navigation">
    @if ($paginator->onFirstPage())
      <button disabled>&laquo;</button>
    @else
      <a href="{{ $paginator->previousPageUrl() }}"><button>&laquo;</button></a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <button disabled>{{ $element }}</button>
      @endif
      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <button class="active">{{ $page }}</button>
          @else
            <a href="{{ $url }}"><button>{{ $page }}</button></a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}"><button>&raquo;</button></a>
    @else
      <button disabled>&raquo;</button>
    @endif
  </nav>
@endif
