@extends('layouts.app_cliente')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 card" style="margin-top: 5px; padding-top: 20px;">
            <div class="row justify-content-center" style="padding: 5px">
                @php(session_start())
                    @foreach($dados as $produto)
                    <div class="col-lg-4 col-sm-6 col-12">
                            <div class="card shadow p-3 mb-5 bg-white rounded">
                                <a href="{{route('produto', $produto->id)}}" style="text-decoration: none">
                                <div class="card-header bg-white" style="padding: 5px; height: 300px">
                                    <img src="{{asset($produto->foto_um)}}" width="100%" height="200px" style="object-fit: cover; object-position: center">
                                    <h6 style="margin: 5px; text-align: justify; color: dimgray;">{{$produto->nome}}</h6>
                                </div>
                                <div class="card-body" style="padding: 5px;">
                                    <p style="margin: 0; padding: 0; font-size: 30px; color: darkslategray; font-weight: bold">R$ {{$produto->preco}}<span style="font-size: 17px"> {{$produto->und_medida}}</span></p>
                                    <p style="margin: 0; padding: 0; color: grey">Até 10x no cartão / 1+3 Crediário</p>
                                    <p style="margin: 0; padding: 0; color: grey">À vista (Desconto especial)</p>
                                </div>
                                </a>
                                <div class="card-footer" align="center" style="padding: 5px">
                                    <button onclick="quantidade({{$produto->id}}, '{{$produto->nome}}')" data-toggle="modal" data-target="#exampleModal"  class="btn btn-primary" style="width: 100%">Adicionar &nbsp;&nbsp;<i class="fas fa-cart-plus"></i></button>
                                </div>
                            </div>
                    </div>
                    @endforeach
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{route("addcart")}}" method="post">
            <div class="modal-body">
                @csrf
                <input type="hidden" id="idproduto" name="id">
                <label>Quantidade</label>
                <input type="number" class="form-control" id="qtd" name="qtd" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Adicionar</button>
            </div>
            </form>
        </div>
    </div>
</div>

    <script type="text/javascript">
        function quantidade(id, nome) {
            document.getElementById("exampleModalLabel").innerHTML = nome;
            document.getElementById("idproduto").value = id;
        }
    </script>
@endsection
