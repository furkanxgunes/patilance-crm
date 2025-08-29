<x-adminlte-card title="Ek Hizmetler / Ürünler" theme="info" icon="fas fa-shopping-bag">

    <div id="extra-items-wrapper">
        {{-- Var olan kayıtlar varsa onları gösterelim --}}
        @if(isset($appointment) && $appointment->extraItems)
            @foreach($appointment->extraItems as $index => $item)
                <div class="extra-item row mb-2">
                    <div class="col-md-5">
                        <input type="text" name="extra_items[{{ $index }}][name]" 
                               class="form-control form-control-sm" 
                               value="{{ $item->name }}" placeholder="Ürün/Hizmet Adı">
                    </div>
                    <div class="col-md-5">
                        <input type="number" step="0.01" min="0"
                               name="extra_items[{{ $index }}][price]" 
                               class="form-control form-control-sm" 
                               value="{{ $item->price }}" placeholder="₺ Tutar">
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-extra-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Yeni ekle butonu --}}
    <button type="button" class="btn btn-outline-success btn-sm mt-2" id="add-extra-item">
        <i class="fas fa-plus"></i> Yeni Ürün/Hizmet Ekle
    </button>

</x-adminlte-card>

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let wrapper = document.getElementById('extra-items-wrapper');
        let addBtn = document.getElementById('add-extra-item');
        let index = wrapper.querySelectorAll('.extra-item').length;

        addBtn.addEventListener('click', function () {
            let row = document.createElement('div');
            row.classList.add('extra-item', 'row', 'mb-2');
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="extra_items[${index}][name]" 
                           class="form-control form-control-sm" 
                           placeholder="Ürün/Hizmet Adı">
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" min="0"
                           name="extra_items[${index}][price]" 
                           class="form-control form-control-sm" 
                           placeholder="₺ Tutar">
                </div>
                <div class="col-md-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm remove-extra-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            wrapper.appendChild(row);
            index++;
        });

        wrapper.addEventListener('click', function (e) {
            if (e.target.closest('.remove-extra-item')) {
                e.target.closest('.extra-item').remove();
            }
        });
    });
</script>
@endpush
