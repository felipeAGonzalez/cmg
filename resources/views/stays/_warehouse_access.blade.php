@if($user->isNurse() && $stay->isActive())
<form method="POST" action="{{ route('stays.warehouse-access', $stay) }}" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-outline-primary">
        <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Solicitar a Almacén
    </button>
</form>
@endif
