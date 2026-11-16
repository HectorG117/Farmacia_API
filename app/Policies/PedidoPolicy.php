<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    /**
     * Determina si el usuario puede ver la lista general.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede consultar un pedido específico.
     * Retorna true únicamente si el pedido pertenece al usuario autenticado.
     */
    public function view(User $user, Pedido $pedido): bool
    {
        return (int) $user->id === (int) $pedido->user_id;
    }

    /**
     * Determina si el usuario autenticado puede crear pedidos.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede actualizar un pedido específico.
     * Retorna true únicamente si el pedido pertenece al usuario autenticado.
     */
    public function update(User $user, Pedido $pedido): bool
    {
        return (int) $user->id === (int) $pedido->user_id;
    }

    /**
     * Determina si el usuario puede cancelar/eliminar un pedido propio.
     * Retorna true únicamente si es el propietario y se encuentra en un estado cancelable.
     */
    public function delete(User $user, Pedido $pedido): bool
    {
        $esPropietario = (int) $user->id === (int) $pedido->user_id;
        $esCancelable = in_array($pedido->estado ?? 'pendiente', ['pendiente', 'registrado']);

        return $esPropietario && $esCancelable;
    }

    /**
     * Determina si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, Pedido $pedido): bool
    {
        return (int) $user->id === (int) $pedido->user_id;
    }

    /**
     * Determina si el usuario puede eliminar permanentemente el modelo.
     */
    public function forceDelete(User $user, Pedido $pedido): bool
    {
        return (int) $user->id === (int) $pedido->user_id;
    }
}