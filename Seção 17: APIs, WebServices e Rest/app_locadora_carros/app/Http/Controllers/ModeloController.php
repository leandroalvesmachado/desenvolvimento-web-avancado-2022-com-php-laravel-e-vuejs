<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Repositories\ModeloRepository;

class ModeloController extends Controller
{
    public function __construct(Modelo $modelo)
    {
        $this->modelo = $modelo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);

        // http://localhost:8000/api/modelos?atributos=id,nome,imagem,marca_id&atributos_marca=nome&filtro=nome

        if ($request->has('atributos_marca')) {
            $atributosMarca = 'marca:id,'.$request->atributos_marca;
            $modeloRepository->selectAtributosRegistrosRelacionados($atributosMarca);
        } else {
            $modeloRepository->selectAtributosRegistrosRelacionados('marca');
        }

        // incompleto, melhorar
        if ($request->has('filtro')) {
            $modeloRepository->filtro($request->filtro);
        }

        if ($request->has('atributos')) {
            $modeloRepository->selectAtributos($request->atributos);
        }

        // if ($request->has('atributos')) {
        //     $atributos = $request->atributos;

        //     // atributos = id,name,imagem,marca_id
        //     // http://localhost:8000/api/modelos?atributos=id,nome,imagem,marca_id
        //     // precisa do marca_id para que o with funcione (a coluna da fk precisa esta no contexto)
        //     // with permite o relacionamento e tb as colunas
        //     $modelos = $modelos->selectRaw($atributos)->get();
        // } else {
        //     // para usar sem o relacionamento marca
        //     // return response()->json($this->modelo->all(), 200);

        //     // para usar o relacionamento na listagem de todos os modelos
        //     // return response()->json($this->modelo->with('marca')->get(), 200);

        //     $modelos = $modelos->get();
        // }

        return response()->json($modeloRepository->getResultado(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public'); // pasta, disco

        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' => $request->abs
        ]);

        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);

        if ($modelo === null) {
            return response()->json([
                'erro' => 'Recurso pesquisado não encontrado'
            ], 404);
        }

        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // imagens no request só funcionam com o método POST, com outros verbos o request file não vai existir
        // para essa situações de atualização de imagens, para no form a chave _method = PUT ou PATCH

        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json([
                'erro' => 'Impossível realizar a atualização. Recurso pesquisado não encontrado.'
            ], 404);
        }

        if ($request->method() === 'PATCH') {
            // PATCH para atualização parcial do recurso
            $regrasDinamicas = [];

            // percorrendo todas as regras definidas no model
            foreach ($modelo->rules() as $input => $regra) {
                // coletar a regra para o campo enviado
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);
        } else {
            // PUT para atualização completa do recurso
            $request->validate($modelo->rules());
        }

        // remove o arquivo antigo, caso um novo arquivo tenha sido enviado no request
        if ($request->file('imagem')) {
            Storage::disk('public')->delete($modelo->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public'); // pasta, disco

        // $modelo->update([
        //     'marca_id' => $request->marca_id,
        //     'nome' => $request->nome,
        //     'imagem' => $imagem_urn,
        //     'numero_portas' => $request->numero_portas,
        //     'lugares' => $request->lugares,
        //     'air_bag' => $request->air_bag,
        //     'abs' => $request->abs
        // ]);

        // preencher o objeto $modelo com os dados do request
        $modelo->fill($request->all());
        $modelo->imagem = $imagem_urn;
        $modelo->save();

        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json([
                'erro' => 'Impossível realizar a exclusão. Recurso pesquisado não encontrado.'
            ], 404);
        }

        // remove o arquivo antigo, caso um novo arquivo tenha sido enviado no request
        Storage::disk('public')->delete($modelo->imagem);

        $modelo->delete();

        return response()->json([
            'msg' => 'O modelo foi removido com sucesso!'
        ], 200);
    }
}
