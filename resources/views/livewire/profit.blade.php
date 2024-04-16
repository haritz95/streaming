<div>
    Profit: $<span id="profit-display">0</span>
</div>

@push('scripts')
    <script>
        window.livewire.on('profitCalculated', profit => {
            document.getElementById('profit-display').textContent = profit;
        });
    </script>
@endpush
