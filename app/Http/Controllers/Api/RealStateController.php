<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\RealStateRequest;
use App\RealState;
use App\User;

class RealStateController extends Controller
{
    private $realState;

    public function __construct(RealState $realState)
    {
        $this->realState = $realState;
    }

    public function index()
    {
        $realStates = auth('api')->user()->real_state();

        return response()->json($realStates->paginate('2'), 200);
    }

    public function store(RealStateRequest $request)
    {
        $data = $request->all();
        $images = $request->file('images');

        try {
            $data['user_id'] = auth('api')->user()->id;

            $realState = $this->realState->create($data); // insercao de dados em massa (mass asignment)

            if(isset($data['categories']) && count($data['categories'])) {
                $realState->categories()->sync($data['categories']); // 1, 2 | 1, 3
            }

            if($images) {
                $imagesUploaded = [];

                foreach ($images as $image) {
                    $path = $image->store('images', 'public');
                    $imagesUploaded[] = ['photo' => $path, 'is_thumb' => false];
                }

                $realState->photos()->createMany($imagesUploaded);
            }

            return response()->json([
                'data' => [
                   'msg' => 'Imóvel cadastrado com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json($message->getMessage(), 401);
        }

    }

    public function update($id, RealStateRequest $request)
    {
        $data = $request->all();
        $images = $request->file('images');

        try {

            $realState = auth('api')->user()->real_state()->findOrFail($id); // busca o id e se nao encontrar retorna um erro

            $realState->update($data);

            if(isset($data['categories']) && count($data['categories'])) {
                $realState->categories()->sync($data['categories']); // 1, 2 | 1, 3   sync faz a cincronizacao
            }

            if($images) {
                $imagesUploaded = [];

                foreach ($images as $image) {
                    $path = $image->store('images', 'public');
                    $imagesUploaded[] = ['photo' => $path, 'is_thumb' => false];
                }

                $realState->photos()->createMany($imagesUploaded);
            }

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel atualizado com sucesso!'
                ]
            ], 200);

        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json($message->getMessage(), 401);
        }
    }

    public function show($id)
    {
        try {

            $realState = auth('api')->user()->real_state()->with('photos')->findOrFail($id); // busca o id e se nao encontrar retorna um erro

            return response()->json([
                    'data' => $realState
            ], 200);

        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json($message->getMessage(), 401);
        }
    }

    public function destroy($id)
    {

        try {

            $realState = auth('api')->user()->real_state()->findOrFail($id); // busca o id e se nao encontrar retorna um erro

            $realState->delete();

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel removido com sucesso!'
                ]
            ], 200);

        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json($message->getMessage(), 401);
        }
    }
}
