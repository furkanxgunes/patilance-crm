@extends('adminlte::page')

@section('title', 'Raporlar')

@section('content_header')
    <h1>Raporlar</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Hizmet Analizi</h3>
                    <p>Hangi hizmetin daha çok verildiğini görüntüleyin</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <a href="{{ route('raporlar.hizmet-analizi') }}" class="small-box-footer">
                    Raporu Görüntüle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Ciro Raporu</h3>
                    <p>Günlük, aylık ve yıllık ciro bilgileri</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('raporlar.ciro-raporu') }}" class="small-box-footer">
                    Raporu Görüntüle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Müşteri Analizi</h3>
                    <p>Sadık müşteriler ve harcamaları</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('raporlar.musteri-analizi') }}" class="small-box-footer">
                    Raporu Görüntüle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .small-box {
            border-radius: .25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            display: block;
            margin-bottom: 20px;
            position: relative;
        }
        .small-box > .inner {
            padding: 10px;
        }
        .small-box h3, .small-box p {
            z-index: 5;
            color: #fff !important;
        }
        .small-box .icon {
            color: rgba(0,0,0,.15);
            z-index: 0;
        }
        .small-box .icon > i {
            font-size: 70px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: all .3s linear;
        }
        .small-box-footer {
            background-color: rgba(0,0,0,.1);
            color: rgba(255,255,255,.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        .small-box-footer:hover {
            background-color: rgba(0,0,0,.15);
            color: #fff;
            text-decoration: none;
        }
    </style>
@endpush
