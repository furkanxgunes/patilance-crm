@extends('adminlte::page')

{{-- Sayfa Başlığı --}}
@section('title', 'Gösterge Paneli')

{{-- İçerik Başlığı --}}
@section('content_header')
    <h1>Gösterge Paneli</h1>
@stop

{{-- Ana İçerik --}}
@section('content')
    <p>Admin paneline hoş geldiniz!</p>
    <div class="card">
        <div class="card-body">
            <p>Burası ana içerik alanıdır. İstediğiniz bileşenleri buraya ekleyebilirsiniz.</p>
        </div>
    </div>

{{-- Example with empty option (for Select) --}}
<x-adminlte-select name="optionsTest1">
    <x-adminlte-options :options="['Option 1', 'Option 2', 'Option 3']" disabled="1"
        empty-option="Select an option..."/>
</x-adminlte-select>

{{-- Example with placeholder (for Select) --}}
<x-adminlte-select name="optionsTest2">
    <x-adminlte-options :options="['Option 1', 'Option 2', 'Option 3']" disabled="1"
        placeholder="Select an option..."/>
</x-adminlte-select>

{{-- Example with empty option (for Select2) --}}
<x-adminlte-select2 name="optionsVehicles" igroup-size="lg" label-class="text-lightblue"
    data-placeholder="Select an option...">
    <x-slot name="prependSlot">
        <div class="input-group-text bg-gradient-info">
            <i class="fas fa-car-side"></i>
        </div>
    </x-slot>
    <x-adminlte-options :options="['Car', 'Truck', 'Motorcycle']" empty-option/>
</x-adminlte-select2>

{{-- Example with multiple selections (for Select) --}}
@php
    $options = ['s' => 'Spanish', 'e' => 'English', 'p' => 'Portuguese'];
    $selected = ['s','e'];
@endphp
<x-adminlte-select id="optionsLangs" name="optionsLangs[]" label="Languages"
    label-class="text-danger" multiple>
    <x-slot name="prependSlot">
        <div class="input-group-text bg-gradient-red">
            <i class="fas fa-lg fa-language"></i>
        </div>
    </x-slot>
    <x-adminlte-options :options="$options" :selected="$selected"/>
</x-adminlte-select>

{{-- Example with multiple selections (for SelectBs) --}}
@php
    $config = [
        "title" => "Select multiple options...",
        "liveSearch" => true,
        "liveSearchPlaceholder" => "Search...",
        "showTick" => true,
        "actionsBox" => true,
    ];
@endphp
<x-adminlte-select-bs id="optionsCategory" name="optionsCategory[]" label="Categories"
    label-class="text-danger" :config="$config" multiple>
    <x-slot name="prependSlot">
        <div class="input-group-text bg-gradient-red">
            <i class="fas fa-tag"></i>
        </div>
    </x-slot>
    <x-adminlte-options :options="['News', 'Sports', 'Science', 'Games']"/>
</x-adminlte-select-bs>

<x-adminlte-card class="bg-gradient-success">NAAAAABER<BR>ASFASFAHAHAHAHA<BR><div>afgasgaas</div></x-adminlte-card>

<div class="card bg-gradient-warning">...</div>
<div class="card bg-gradient-danger">...</div>
<div class="card bg-gradient-dark">..sfw44444efgedgdsgsdgsgs<br>ergergregherhreherherherh.</div>
    <!-- Main node for this component -->
<div class="timeline">
  <!-- Timeline time label -->
  <div class="time-label">
    <span class="bg-green">23 Aug. 2019</span>
  </div>
  <div>
  <!-- Before each timeline item corresponds to one icon on the left scale -->
    <i class="fas fa-envelope bg-blue"></i>
    <!-- Timeline item -->
    <div class="timeline-item">
    <!-- Time -->
      <span class="time"><i class="fas fa-clock"></i> 12:05</span>
      <!-- Header. Optional -->
      <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>
      <!-- Body -->
      <div class="timeline-body">
        Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
        weebly ning heekya handango imeem plugg dopplr jibjab, movity
        jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
        quora plaxo ideeli hulu weebly balihoo...
      </div>
      <!-- Placement of additional controls. Optional -->
      <div class="timeline-footer">
        <a class="btn btn-primary btn-sm">Read more</a>
        <a class="btn btn-danger btn-sm">Delete</a>
      </div>
    </div>
  </div>
  <!-- The last icon means the story is complete -->
  <div>
    <i class="fas fa-clock bg-gray"></i>
  </div>
</div>
@stop



{{-- Sayfaya özel CSS eklemek için --}}
@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

{{-- Sayfaya özel JS eklemek için --}}
@section('js')
    <script> console.log('Sayfa yüklendi!'); </script>
@stop