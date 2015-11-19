<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class ResourceCrudControllerA
 *
 * @TODO ability to split validation for update (PUT and PATCH) and create
 * @TODO we need to decide which methods we can ovwerrite and which we cannot
 *
 * @package App\Http\Controllers\Dashboard
 */
class CrudController extends Controller
{
    protected $name = null;

    // If resource has parent resource (eg article.section) set parent name here
    protected $parentName = null;

    // Set this properties to models name only if the model name differs form "\App\Entities\{ucfirst($this->name)}". e.g. '\App\SomeNamespace\Entity'
    protected $modelName = null;
    protected $parentModelName = null;

    protected $rules = [];

    // Set to integer (items per page) for pagination.
    protected $paginate = null;

    public function index()
    {
        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));
        $entities = $this->getEntities($parent);

        return view($this->getViewName('index'), ['entities' => $entities, 'parent' => $parent, 'meta' => ['is_pagination' => !!$this->paginate]]); // @TODO can we exclude is_pagination param?
    }

    public function show()
    {
        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));
        $entity = $this->getEntity($this->getRouteParam($this->name));

        return view($this->getViewName('show'), ['entity' => $entity, 'parent' => $parent]);
    }

    public function edit()
    {
        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));
        $entity = $this->getEntity($this->getRouteParam($this->name));

        return view($this->getViewName('edit'), ['entity' => $entity, 'parent' => $parent]);
    }

    public function create()
    {
        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));
        $entity = $this->buildEntity();

        return view($this->getViewName('create'), ['entity' => $entity, 'parent' => $parent]);
    }


    public function store(Request $request)
    {
        $this->validate($request, $this->getRules('store'));

        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));
        $entity = $this->buildEntity();
        // both lines can be replaced by $entity->update($request->all());
        $entity->fill($request->all());
        $entity->save();

        return redirect($this->getRoute('edit', $entity, $parent))->with('alert-success', 'Saved');
    }

    public function update(Request $request)
    {
        $this->validate($request, $this->getRules('update'));

        $entity = $this->getEntity($this->getRouteParam($this->name));
        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));

        $entity->fill($request->all());
        $entity->save();

        return redirect($this->getRoute('edit', $entity, $parent))->with('alert-success', 'Saved');
    }

    public function destroy()
    {
        /** @var Model $entity */
        $entity = $this->getEntity($this->getRouteParam($this->name));
        $parent = $this->getParentEntity($this->getRouteParam($this->parentName));

        $entity->delete();

        return redirect($this->getRoute('index', $entity, $parent))->with('alert-success', 'Deleted');
    }

    /**
     * Returns query for an entity
     * If you need eager-loading or some checks, you should do it here
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getEntityQuery()
    {
        $model = $this->getModelName();

        return $model::query();
    }

    /**
     * Returns query для for entities list
     * If you need eager-loading or some checks, you should do it here
     *
     * @param Model|null $parent
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getEntitiesQuery($parent = null)
    {
        $model = $this->getModelName();
        $query = $model::query();

        if ($parent !== null) {
            $query->where($this->getEntityParentProperty(), $parent->getKey());
        }

        return $query;
    }

    /**
     * @param $key
     *
     * @return Model
     */
    protected function getEntity($key)
    {
        $query = $this->getEntityQuery();

        return $query->findOrFail($key);
    }

    /**
     * @param Model|null $parent
     *
     * @return Collection|static[]
     */
    protected function getEntities($parent = null)
    {
        $query = $this->getEntitiesQuery($parent);

        if ($this->paginate !== null) {
            $entities = $query->paginate($this->paginate);
        } else {
            $entities = $query->get();
        }

        return $entities;
    }

    /**
     * @return Model
     */
    protected function buildEntity()
    {
        $model = $this->getModelName();

        return new $model;
    }

    /**
     * @param $key
     *
     * @return Model
     */
    protected function getParentEntity($key)
    {
        if ($key === null) {
            return null;
        }

        /** @var Model $model */
        $model = $this->getParentModelName();

        return $model::query()->findOrFail($key);
    }

    /**
     * Route::resource('something', 'SomethingController') maps /something/edit to public function edit($something),
     * and in general we can't add variable name to controller method, so we get this parameter value from route by name
     *
     * @param string $name
     *
     * @return null|object|string
     */
    protected function getRouteParam($name)
    {
        if ($name === null) {
            return null;
        } else {
            return $this->getRouter()->current()->getParameter($name);
        }
    }

    /**
     * @return string|Model
     */
    protected function getModelName()
    {
        return $this->modelName ?: '\App\Entities\\' . ucfirst($this->name);
    }

    /**
     * @return string|Model
     */
    protected function getParentModelName()
    {
        return $this->parentModelName ?: '\App\Entities\\' . ucfirst($this->parentName);
    }

    /**
     * @return string|Model
     */
    protected function getEntityParentProperty()
    {
        return $this->parentName . '_id';
    }

    /**
     * Validation rules for the given action. If rules for actions are different, choose rules by action name by overwriting this method
     *
     * @param string $action action name: 'store' or 'update'
     *
     * @return array
     */
    protected function getRules ($action) {
        switch ($action) {
            case 'store': // no break
            case 'update': // no break
            default:
                return $this->rules;
        }
    }

    /**
     * By default `show`, `create` and `edit` views is points to `.show` view. Feel free to overwrite
     *
     * @param string $action
     *
     * @return string
     */
    protected function getViewName($action)
    {
        switch ($action) {
            case 'show': // no break
            case 'create': // no break
            case 'edit':
                return "dashboard.{$this->name}.show";
                break;
            default:
                return "dashboard.{$this->name}.{$action}";
                break;
        }
    }

    /**
     * @param string $action
     * @param Model $entity
     * @param Model $parent
     *
     * @return string
     */
    protected function getRoute($action, $entity, $parent = null)
    {
        $routeParameters = $action === 'index' ? [] : [$entity->getKey()];

        if ($parent !== null) {
            $routeName = "dashboard.{$this->parentName}.{$this->name}.{$action}";
            array_unshift($routeParameters, $parent->getKey());
        } else {
            $routeName = "dashboard.{$this->name}.{$action}";
        }

        return route($routeName, $routeParameters);
    }
}