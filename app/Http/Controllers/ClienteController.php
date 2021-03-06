<?php

namespace App\Http\Controllers;

use App\BannerModel;
use App\CategoriaModel;
use App\PedidoModel;
use App\ProdutoModel;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class ClienteController extends Controller
{

    public function principal()
    {
        $dados = ProdutoModel::orderByRaw('RAND()')->take(12)->get();
        $banners = BannerModel::all();
        return view('client.principal', compact('dados'), compact('banners'));
    }


    public function produto($id){
        $produto = ProdutoModel::findOrFail($id);
        return view('client.produtoview', compact('produto'));
    }

    public function buscarProdutoNome(Request $request)
    {
        $dados = ProdutoModel::where('nome', 'like', '%' . $request->produto . '%')->get();
        $busca = "Resultados para: ". $request->produto;
        return view('client.produtos', compact('dados', 'busca'));
    }

    public function buscarProdutoCategoria($id)
    {

        $categoria = CategoriaModel::findOrFail($id);
        $dados = ProdutoModel::all()->where('id_categoria', $id);
        $busca = 'Filtro Categoria: '. $categoria->nome;
        return view('client.produtos', compact('dados', 'busca'));
    }

    public function addCart(Request $request)
    {
        $id = $request['id'];
        $qtd = $request['qtd'];
        session_start();


        if(isset($_SESSION['carrinho'])){
            if(isset($_SESSION['carrinho'][$id])){
//                return $_SESSION['carrinho'][$id];
                $_SESSION['carrinho'][$id] += $qtd;
            }else{
                $_SESSION['carrinho'][$id] =  $qtd;
            }
        }else{
            $_SESSION['carrinho'] = [];
            $_SESSION['carrinho'] [$id] = $qtd;
        }
        flash("Item Adicionado")->success();
        return redirect()->route('carrinho');

    }

    public function removecart($id, $qtd)
    {
        session_start();
        if ($qtd==0){
            unset($_SESSION['carrinho'][$id]);
            flash("Item Removido")->success();
        }else{
            $_SESSION['carrinho'][$id] = $qtd;
            flash("Quantidade Alterada")->success();
        }

        return redirect()->route('carrinho');
    }

    public function carrinho()
    {

        $total = self::pegartotalContavel();

//        $total = 10.00;
        return view('client.carrinho', compact('total'));
    }

    public function enviar(Request $request)
    {

        session_start();

        $pedido = new PedidoModel();

        $nome = $request['nome'];
        $telefone = $request['telefone'];
        $endereco = $request['endereco'];
        $retirar = $request['retirar'];
        $obs = $request['obs'];
        $pagamento = $request['pagamento'];
//        $profissional = $request['profissional'];
//        $irmaos = $request['irmaos'];
        $parc_cart = $request['parc_cartao'];
        $parc_cred = $request['parc_cred'];
        $troco = $request['troco'];
        $cpf = $request['cpf'];
        $cupom = $request['cupom'];

        if($retirar == "sim"){
            $endereco = "Retirar no Estabelecimento";
        }

        if ($pagamento == "Cartão") {
            $detalhespay = $parc_cart;
        }else if ($pagamento == "A vista") {
            $detalhespay = "Troco para ".$troco;
        }else if ($pagamento == "Crediário") {
            $detalhespay = $parc_cred . " - CPF:".$cpf;
        }

        $itens = "";
        $texto = "🛍 *NOVO PEDIDO*%0aDanyllo Retalhos%0a".$cupom."%0a%0a👤 *Cliente:* ".$nome."%0a%f0%9f%93%8d _".$endereco. "_%0a".$telefone."%0a%0a📦 *Produtos*%0a------------------------%0a";

//        $ino = "[ Não faz parte. ]";
//        if ($irmaos=="sim"){
//            $ino = "[ ".$profissional .". ]";
//        }

        foreach ( $_SESSION['carrinho'] as $id => $qtd){
            $dado = ProdutoModel::findOrFail($id);
            $texto = $texto . "%e2%80%a2%20" . $dado->codigosistema ."%0a".  $dado->nome."%0aQtd: ". $qtd . "%0aR$ ".$dado->preco ."%0a%0a";
            $itens = $itens . " (".$dado->codigosistema." - ".  $qtd .'x '. $dado->nome ." - ".$dado->preco.") ";
        }

        $texto = $texto . "------------------------%0a*Total:* R$ ".self::pegartotal()." %0a------------------------%0a💳 *Forma de Pagamento:* ".$pagamento."%0a" . $detalhespay ."%0a%0a*CUPOM: " . $cupom . "%0aObservações:* ".$obs ."%0a------------------------%0a". date('d/m/Y h:i:s A');



        $pedido->cliente = $nome;
        $pedido->entrega = $endereco;
        $pedido->formadepagamento = $pagamento . " - " .str_replace("%2B", "+", $detalhespay);
        $pedido->obs = $obs;
        $pedido->telefone = $telefone;
        $pedido->cupom = $cupom;
        $pedido->total = self::pegartotal();
        $pedido->itens = $itens;
        $pedido->save();

        $url = 'https://api.whatsapp.com/send?phone=5583996551689&text='. $texto;
        return view('client.finish', compact('url'));
//        return redirect(url('https://api.whatsapp.com/send?phone='.DadosCliente::$telefone_loja.'&text='. $texto));
    }

    public function limpar()
    {
        session_start();
        session_destroy();
        return redirect()->route('carrinho');
    }

    public static function pegarcarrinho(){
        if(isset($_SESSION['carrinho'])){
            return count($_SESSION['carrinho']);
        } else{
            return 0;
        }
    }

    public static function pegarcarrinhoNotificacao(){
        if(isset($_SESSION['carrinho'])){
            $qtd = count($_SESSION['carrinho']);
            if ($qtd>0){
                if($qtd>1){
                    return $qtd . " Itens";
                }else{
                    return $qtd . " Item";
                }

            }else{
                return "Vazio";
            }
        } else{
            return "Vazio";
        }
    }

    public static function pegartotal(){
        $total=0.00;
        foreach ( $_SESSION['carrinho'] as $id => $qtd){
            $dado = ProdutoModel::findOrFail($id);
            $total += (str_replace(',', '.', str_replace('.', '', $dado->preco)) * str_replace(',', '.', $qtd));
        }
        return number_format($total, 2, ',', '.');
//        return number_format($_SESSION['total'], 2, ',', '.');
    }

    public static function pegartotalContavel(){
        $total=0.00;
        session_start();



        if(isset($_SESSION['carrinho'])){
            foreach ( $_SESSION['carrinho'] as $id => $qtd){
                $dado = ProdutoModel::findOrFail($id);
//                return $dado;
                $total += (str_replace(',', '.', str_replace('.', '', $dado->preco)) * str_replace(',', '.', $qtd));
            }
        }


        return number_format($total, 2, '.', '');
//        return number_format($_SESSION['total'], 2, ',', '.');
    }



    public function listarUsuarios(){
        $dados = User::all();
         return view('admin.usuarios_list', compact('dados'));
    }

    public function alterarStatus(Request $request, $id){
        $dado = PedidoModel::findOrFail($id);
        $dado->status = $request['status'];
        $dado->id_user = Auth::user()->id;
        $dado->save();
        flash("Status Alterado com Sucesso")->success();
        return redirect()->route('adminpedidos.index');
    }
}
