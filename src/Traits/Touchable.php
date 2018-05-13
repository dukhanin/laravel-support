<?php
namespace Dukhanin\Support\Traits;

/**
 * Трейт, предоставляющий методы для регистрации изменений объекта
 * после его создания.
 *
 * Полезен при работе с объектами-хранилищами, изменения в которых
 * никак не регистрируется (например, Illuminate\Database\Eloquent\Collection),
 * особенно в случаях их отложенной инициализации "по требованию"
 * 
 * <code>
 * function addCustomField($key, $value) {
 *     if(!$collection->touched()) {
 *         $collection->put('name', 'Name');
 *         $collection->put('surname', 'Surname');
 *
 *         $collection->touch();
 *     }
 * 
 *     $collection->put($key, $value);
 * }
 * </code>
 */
trait Touchable
{
    /**
     * Произошло ли какое-либо изменение объекта?
     *
     * @var bool
     */
    protected $touched = false;

    /**
     * Отметить изменение объекта
     *
     * @return void
     */
    public function touch()
    {
        $this->touched = true;
    }

    /**
     * Произошло ли какое-либо изменение объекта?
     *
     * @return bool
     */
    public function touched()
    {
        return $this->touched;
    }
}