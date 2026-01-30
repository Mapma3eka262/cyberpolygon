<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-danger btn-sm" title="Выход">
        <i class="fas fa-sign-out-alt"></i> Выход
    </button>
</form>