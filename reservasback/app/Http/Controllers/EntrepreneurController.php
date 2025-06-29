<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Entrepreneur;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EntrepreneurController extends Controller
{
    /**
     * Listar emprendedores con filtros y relaciones.
     */
    public function index(Request $request)
    {
        $query = Entrepreneur::with(['user', 'association', 'place', 'categories']);

        if ($request->has('association_id')) {
            $query->where('association_id', $request->association_id);
        }

        if ($request->has('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Crear nuevo emprendedor.
     */
    public function store(Request $request)
    {
        $isAdminFlow = !$request->filled('user_id');

        $rules = $isAdminFlow
            ? [
                'name'           => 'required|string|max:255',
                'email'          => 'required|email|unique:users,email',
                'business_name'  => 'required|string|max:150',
                'ruc'            => 'nullable|string|size:11|unique:entrepreneurs,ruc',
                'phone'          => 'required|string|max:15',
                'description'    => 'nullable|string',
                'association_id' => 'nullable|exists:associations,id',
                'place_id'       => 'nullable|exists:places,id',
                'latitude'       => 'nullable|numeric',
                'longitude'      => 'nullable|numeric',
                'district'       => 'nullable|string|max:100',
                'profile_image'  => 'nullable|image|max:2048',
                'categories'     => 'array',
                'categories.*'   => 'exists:categories,id',
            ]
            : [
                'user_id'        => 'required|exists:users,id|unique:entrepreneurs,user_id',
                'business_name'  => 'required|string|max:150',
                'ruc'            => 'nullable|string|size:11|unique:entrepreneurs,ruc',
                'phone'          => 'required|string|max:15',
                'description'    => 'nullable|string',
                'association_id' => 'nullable|exists:associations,id',
                'place_id'       => 'nullable|exists:places,id',
                'latitude'       => 'nullable|numeric',
                'longitude'      => 'nullable|numeric',
                'district'       => 'nullable|string|max:100',
                'profile_image'  => 'nullable|image|max:2048',
                'categories'     => 'array',
                'categories.*'   => 'exists:categories,id',
            ];

        $data = $request->validate($rules);

        DB::beginTransaction();
        try {
            if ($isAdminFlow) {
                $password = Str::random(10);
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($password),
                ]);

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('emprendedor');
                }

                $data['user_id'] = $user->id;
            }

            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $request->file('profile_image')->store('entrepreneurs', 'public');
            }

            $entrepreneurData = array_merge($data, ['status' => 'activo']);
            $entrepreneur = Entrepreneur::create($entrepreneurData);

            if (!empty($data['categories'])) {
                $entrepreneur->categories()->sync($data['categories']);
            }

            DB::commit();

            $response = [
                'message' => 'Emprendedor creado exitosamente',
                'entrepreneur' => $entrepreneur->load(['user', 'association', 'place', 'categories']),
                'entrepreneur_id' => $entrepreneur->id,
                'user_id' => $user->id ?? $entrepreneur->user_id,
            ];

            if ($isAdminFlow) {
                $response['user'] = $user;
                $response['password'] = $password;
            }

            return response()->json($response, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear emprendedor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un emprendedor.
     */
    public function show(Entrepreneur $entrepreneur)
    {
        return response()->json(
            $entrepreneur->load(['user', 'association', 'place', 'categories'])
        );
    }

    /**
     * Actualizar perfil de emprendedor.
     */
    public function update(Request $request, Entrepreneur $entrepreneur)
    {
        $rules = [
            'business_name'  => 'sometimes|string|max:150',
            'ruc'            => 'sometimes|string|size:11|unique:entrepreneurs,ruc,' . $entrepreneur->id,
            'phone'          => 'sometimes|string|max:15',
            'description'    => 'nullable|string',
            'association_id' => 'nullable|exists:associations,id',
            'place_id'       => 'nullable|exists:places,id',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'district'       => 'nullable|string|max:100',
            'profile_image'  => 'nullable|image|max:2048',
            'categories'     => 'sometimes|array',
            'categories.*'   => 'exists:categories,id',
            'name'           => 'sometimes|string|max:255',
            'email'          => 'sometimes|email|unique:users,email,' . $entrepreneur->user_id,
            'status'         => 'sometimes|in:activo,inactivo,suspendido',
        ];

        $data = $request->validate($rules);

        DB::beginTransaction();
        try {
            $user = $entrepreneur->user;
            if (isset($data['name']) || isset($data['email'])) {
                $user->update(array_filter([
                    'name'  => $data['name'] ?? null,
                    'email' => $data['email'] ?? null,
                ]));
            }

            if ($request->hasFile('profile_image')) {
                if ($entrepreneur->profile_image) {
                    Storage::disk('public')->delete($entrepreneur->profile_image);
                }
                $data['profile_image'] = $request->file('profile_image')->store('entrepreneurs', 'public');
            }

            $entrepreneur->update($data);

            if (!empty($data['categories'])) {
                $entrepreneur->categories()->sync($data['categories']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Emprendedor actualizado',
                'data'    => $entrepreneur->fresh()->load(['user', 'association', 'place', 'categories']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar emprendedor.
     */
    public function destroy(Entrepreneur $entrepreneur)
    {
        DB::beginTransaction();
        try {
            $user = $entrepreneur->user;
            if ($user->entrepreneur()->count() == 1) {
                $user->delete();
            }

            if ($entrepreneur->profile_image) {
                Storage::disk('public')->delete($entrepreneur->profile_image);
            }
            $entrepreneur->delete();

            DB::commit();
            return response()->json(['message' => 'Emprendedor eliminado']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener emprendedores por categoría.
     */
    public function byCategory($categoryId)
    {
        $list = Entrepreneur::whereHas('categories', fn($q) => $q->where('categories.id', $categoryId))
            ->with(['user', 'place', 'categories'])
            ->get();
        return response()->json($list);
    }

    /**
     * Cambiar estado del emprendedor.
     */
    public function toggleStatus(Entrepreneur $entrepreneur)
    {
        DB::beginTransaction();

        try {
            $newStatus = $this->getNextStatus($entrepreneur->status);

            if (!$newStatus) {
                throw new \Exception("Estado no válido para el emprendedor.");
            }

            $entrepreneur->update(['status' => $newStatus]);

            DB::commit();

            return response()->json([
                'message' => 'Estado del emprendedor actualizado',
                'entrepreneur' => $entrepreneur->load(['user', 'association', 'place', 'categories']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el estado del emprendedor',
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }

    /**
     * Obtener el siguiente estado.
     */
    private function getNextStatus($currentStatus)
    {
        switch ($currentStatus) {
            case 'activo':
                return 'suspendido';
            case 'suspendido':
                return 'activo';
            default:
                throw new \Exception("Estado inválido: $currentStatus");
        }
    }


    /**
     * Mostrar emprendedor autenticado.
     */
    public function showAuthenticatedEntrepreneur()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            $entrepreneur = $user->entrepreneur;
            if (!$entrepreneur) {
                return response()->json(['message' => 'Perfil de emprendedor no encontrado'], 404);
            }

            $entrepreneur = Entrepreneur::with(['user','association','place','categories'])
                ->findOrFail($entrepreneur->id);

            $history = method_exists($entrepreneur, 'activities')
                ? $entrepreneur->activities()->orderBy('created_at','desc')->get(['created_at','type','action','details'])
                : [
                    ['created_at'=>$entrepreneur->created_at,'type'=>'Creación','action'=>'Creación de perfil','details'=>'Perfil inicial creado'],
                    ['created_at'=>$entrepreneur->updated_at,'type'=>'Actualización','action'=>'Última actualización','details'=>'Perfil actualizado'],
                ];

            return response()->json(compact('entrepreneur','history'));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error interno',
                'error'   => $e->getMessage(),
                'trace'   => array_slice($e->getTrace(), 0, 3),
            ], 500);
        }
    }

    public function history(Entrepreneur $entrepreneur)
    {
        $history = [
            [
                'created_at' => $entrepreneur->created_at,
                'action'     => 'Creación de perfil',
                'details'    => 'Perfil inicial creado',
            ],
            [
                'created_at' => $entrepreneur->updated_at,
                'action'     => 'Última actualización',
                'details'    => 'Perfil actualizado por admin o usuario',
            ],
        ];

        return response()->json([
            'entrepreneur' => $entrepreneur->load(['user','association','place','categories']),
            'history'      => $history,
        ]);
    }

    public function count()
    {
        $count = Entrepreneur::count();
        return response()->json(['count' => $count]);
    }

    public function showAuthenticated()
    {
        return response()->json(auth()->user());
    }

    public function getCategories($entrepreneur_id)
    {
        $entrepreneur = Entrepreneur::find($entrepreneur_id);
        if (!$entrepreneur) {
            return response()->json(['message' => 'Emprendedor no encontrado'], 404);
        }

        $categories = $entrepreneur->categories;
        return response()->json($categories);
    }

    public function myProducts()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        if (!$user->entrepreneur) {
            return response()->json(['message' => 'No tienes un perfil de emprendedor'], 403);
        }

        $entrepreneurId = $user->entrepreneur->id;

        $products = Product::with(['categories', 'place', 'entrepreneur'])
            ->where('entrepreneur_id', $entrepreneurId)
            ->get();

        return response()->json($products);
    }

    public function authenticated()
    {
        try {
            $entrepreneur = auth()->user()->entrepreneur;

            if (!$entrepreneur) {
                return response()->json(['error' => 'No se encontró el emprendedor'], 404);
            }

            return response()->json([
                'entrepreneur' => $entrepreneur->load('user'),
                'history' => $entrepreneur->history()->latest()->get()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en authenticated(): ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
