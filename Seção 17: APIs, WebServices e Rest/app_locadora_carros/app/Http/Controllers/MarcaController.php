<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json($this->marca->all(), 200);
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
        // validações colocadas no model
        // $regras = [
        //     'nome' => 'required|unique:marcas',
        //     'imagem' => 'required'
        // ];

        // $feedback = [
        //     'required' => 'O campo :attribute é obrigatório',
        //     'nome.unique' => 'O nome da marca já existe'
        // ];

        $request->validate($this->marca->rules(), $this->marca->feedback());
        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public'); // pasta, disco

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);

        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json([
                'erro' => 'Recurso pesquisado não encontrado'
            ], 404);
        }

        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // imagens no request só funcionam com o método POST, com outros verbos o request file não vai existir
        // para essa situações de atualização de imagens, para no form a chave _method = PUT ou PATCH
        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json([
                'erro' => 'Impossível realizar a atualização. Recurso pesquisado não encontrado.'
            ], 404);
        }

        if ($request->method() === 'PATCH') {
            // PATCH para atualização parcial do recurso
            $regrasDinamicas = [];

            // percorrendo todas as regras definidas no model
            foreach ($marca->rules() as $input => $regra) {
                // coletar a regra para o campo enviado
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas, $marca->feedback());
        } else {
            // PUT para atualização completa do recurso
            $request->validate($marca->rules(), $marca->feedback());
        }

        // remove o arquivo antigo, caso um novo arquivo tenha sido enviado no request
        if ($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public'); // pasta, disco

        $marca->update([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);

        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json([
                'erro' => 'Impossível realizar a exclusão. Recurso pesquisado não encontrado.'
            ], 404);
        }

        // remove o arquivo antigo, caso um novo arquivo tenha sido enviado no request
        Storage::disk('public')->delete($marca->imagem);

        $marca->delete();

        return response()->json([
            'msg' => 'A marca foi removida com sucesso!'
        ], 200);
    }
}
