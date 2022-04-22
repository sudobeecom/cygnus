<?php

namespace SudoBee\Cygnus\Resource;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Response;
use SudoBee\Cygnus\Core\Traits\HasRegisterRoutes;
use SudoBee\Cygnus\Core\Traits\HasResolveHelpers;
use SudoBee\Cygnus\Core\Utilities\Notification;
use SudoBee\Cygnus\Core\Utilities\RouteUtility;
use SudoBee\Cygnus\Core\Utilities\Translate;
use SudoBee\Cygnus\Resource\Actions\HandleResourceRouteIndexAction;
use SudoBee\Cygnus\Resource\Utilities\ResourceFormFactory;
use SudoBee\Cygnus\Form\Form;
use SudoBee\Cygnus\Form\Traits\HasCanHaveStore;
use SudoBee\Cygnus\Responses\StructuredPageResponse;

/**
 * @template TModelClass of Model
 */
abstract class Resource
{
	use HasRegisterRoutes;
	use HasCanHaveStore;
	use HasResolveHelpers;

	/** @var class-string<TModelClass> $modelClass */
	private string $modelClass;

	/** @var Builder<TModelClass>|BelongsTo<Model, TModelClass>|HasMany<TModelClass> $query */
	private Builder|BelongsTo|HasMany $query;

	/** @var TModelClass $model */
	private Model $model;

	private function __construct()
	{
		$this->bootHasCanHaveStore();
	}

	/**
	 * @return class-string<TModelClass>
	 */
	abstract protected function model(): string;

	/**
	 * @param Builder<TModelClass> $query
	 * @return Builder<TModelClass>|BelongsTo<Model, TModelClass>|HasMany<TModelClass>
	 */
	protected function query(Builder $query): Builder|BelongsTo|HasMany
	{
		return $query;
	}

	protected function getRouteKeyName(): string
	{
		return $this->getModel()->getRouteKeyName();
	}

	/**
	 * @return class-string<TModelClass>
	 */
	private function getModelClass(): string
	{
		if (!isset($this->modelClass)) {
			$this->modelClass = $this->model();
		}

		return $this->modelClass;
	}

	/**
	 * @return TModelClass
	 */
	private function getModel(): Model
	{
		if (!isset($this->model)) {
			$modelClass = $this->getModelClass();

			$this->model = new $modelClass();
		}

		return $this->model;
	}

	/**
	 * @return Builder<TModelClass>|BelongsTo<Model, TModelClass>|HasMany<TModelClass>
	 */
	private function getQuery(): Builder|BelongsTo|HasMany
	{
		if (!isset($this->query)) {
			$rawQuery = $this->model()::query();

			$this->query = $this->query($rawQuery);
		}

		return $this->query;
	}

	/**
	 * @param array<string, mixed> $validated
	 * @return array<string, mixed>
	 */
	protected function parseValidated(array $validated): array
	{
		return $validated;
	}

	public function registerRoutes(): void
	{
		$modelCamelName = $this->getModelCamel();

		RouteUtility::get(
			$this->createRouteLink(),
			fn() => $this->handleIndex()
		);

		RouteUtility::get(
			$this->createRouteLink("/create"),
			fn() => $this->handleCreate()
		);
		RouteUtility::get(
			$this->createRouteLink("/{" . $modelCamelName . "}/edit"),
			fn() => $this->handleEdit()
		);

		RouteUtility::post(
			$this->createRouteLink("/create"),
			fn(Request $request) => $this->handleInsert($request)
		);
		RouteUtility::post(
			$this->createRouteLink("/{" . $modelCamelName . "}/update"),
			fn(Request $request) => $this->handleUpdate($request)
		);
		RouteUtility::post(
			$this->createRouteLink("/{" . $modelCamelName . "}/delete"),
			fn() => $this->handleDelete()
		);
	}

	/**
	 * @throws Exception
	 */
	private function handleIndex(): Response
	{
		$this->updateStore();

		return app(HandleResourceRouteIndexAction::class)->execute(
			response: $this->buildResponse(),
			query: $this->getQuery(),
			model: $this->getModel(),
			modelHeadline: $this->getModelHeadline(),
			routeCreateLink: $this->createRouteLink("/create"),
			routeEditLink: $this->createRouteLink("/{model}/edit"),
			routeDeleteLink: $this->createRouteLink("/{model}/delete"),
			modelRouteKeyName: $this->getRouteKeyName()
		);
	}

	private function handleCreate(): Response
	{
		return $this->buildResponse()
			->setNodes([$this->getCreateForm()])
			->export();
	}

	/**
	 * @throws Exception
	 */
	private function handleEdit(): Response
	{
		return $this->buildResponse()
			->setNodes([$this->getEditForm()])
			->export();
	}

	/**
	 * @throws Exception
	 */
	private function handleInsert(Request $request): RedirectResponse
	{
		$this->updateStore();

		$validated = $this->validate(
			request: $request,
			form: $this->getCreateForm()
		);

		$this->getQuery()->create($this->parseValidated($validated));

		Notification::success(
			$this->getModelHeadline() .
				" " .
				Translate::text("has been created.")
		);

		return redirect()->to($this->createRouteLink());
	}

	/**
	 * @throws Exception
	 */
	private function handleUpdate(Request $request): RedirectResponse
	{
		$this->updateStore();
		$entity = $this->resolveEntity();

		$validated = $this->validate(
			request: $request,
			form: $this->getEditForm()
		);

		$entity->update($this->parseValidated($validated));

		Notification::success(
			$this->getModelHeadline() .
				" " .
				Translate::text("has been updated.")
		);

		return redirect()->to($this->createRouteLink());
	}

	private function handleDelete(): RedirectResponse
	{
		$this->updateStore();

		$this->resolveEntity()->delete();

		Notification::success(
			$this->getModelHeadline() .
				" " .
				Translate::text("has been deleted.")
		);

		return redirect()->to($this->createRouteLink());
	}

	private function getCreateForm(): Form
	{
		$this->updateStore();

		return ResourceFormFactory::make(
			model: $this->getModel(),
			entity: null,
			actionLink: $this->createRouteLink("/create"),
			modelHeadline: $this->getModelHeadline()
		);
	}

	/**
	 * @throws Exception
	 */
	private function getEditForm(): Form
	{
		$this->updateStore();

		$entity = $this->resolveEntity();

		return ResourceFormFactory::make(
			model: $this->getModel(),
			entity: $entity,
			actionLink: $this->createRouteLink(
				"/" . $entity->{$this->getRouteKeyName()} . "/update"
			),
			modelHeadline: $this->getModelHeadline()
		);
	}

	private function resolveEntity(): Model
	{
		return $this->resolve(
			class: get_class($this->getModel()),
			query: $this->getQuery(),
			modelRouteKeyName: $this->getRouteKeyName()
		);
	}

	/**
	 * @param Request $request
	 * @param Form $form
	 * @return array<string, mixed>
	 * @throws Exception
	 */
	private function validate(Request $request, Form $form): array
	{
		$inputs = $request->all();

		return Validator::validate(
			$inputs,
			$form->getValidationRules((object) $inputs)
		);
	}

	private function createRouteLink(string $subLink = ""): string
	{
		return "/" .
			((string) Str::of($this->getModelBasename())
				->plural()
				->kebab()) .
			$subLink;
	}

	private function getModelBasename(): string
	{
		return Str::of($this->getModelClass())->classBasename();
	}

	private function getModelHeadline(): string
	{
		return Str::of($this->getModelBasename())
			->headline()
			->lower()
			->ucfirst();
	}

	private function getModelCamel(): string
	{
		return Str::of($this->getModelBasename())->camel();
	}

	private function buildResponse(): StructuredPageResponse
	{
		$pageTitle = Str::of($this->getModelHeadline())->plural();

		return StructuredPageResponse::make()->setTitle($pageTitle);
	}
}
