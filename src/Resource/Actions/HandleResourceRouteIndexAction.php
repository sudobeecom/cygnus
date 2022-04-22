<?php

namespace SudoBee\Cygnus\Resource\Actions;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Inertia\Response;
use SudoBee\Cygnus\Component\Components\Button\Button;
use SudoBee\Cygnus\Component\Components\Link\Link;
use SudoBee\Cygnus\Component\Components\PaginatedTable\PaginatedTable;
use SudoBee\Cygnus\Component\Components\Table\Cells\TableLinks;
use SudoBee\Cygnus\Component\Components\Table\TableRow;
use SudoBee\Cygnus\Component\Enums\Icon;
use SudoBee\Cygnus\Core\Utilities\Translate;
use SudoBee\Cygnus\Responses\StructuredPageResponse;

class HandleResourceRouteIndexAction
{
	/**
	 * @param StructuredPageResponse $response
	 * @param Builder<Model>|BelongsTo<Model, Model>|HasMany<Model> $query
	 * @param Model $model
	 * @param string $modelHeadline
	 * @param string $routeCreateLink
	 * @param string $routeEditLink
	 * @param string $routeDeleteLink
	 * @param string|null $modelRouteKeyName
	 * @return Response
	 * @throws Exception
	 */
	public function execute(
		StructuredPageResponse $response,
		Builder|Relation|HasMany $query,
		Model $model,
		string $modelHeadline,
		string $routeCreateLink,
		string $routeEditLink,
		string $routeDeleteLink,
		?string $modelRouteKeyName
	): Response {
		$columnKeys = collect($model->getFillable())->diff($model->getHidden());

		$paginatedTable = PaginatedTable::make()
			->setTitle(Str::of($modelHeadline)->plural())
			->setPanelActions([
				Button::make()
					->setTitle(
						Translate::text("Create new") .
							" " .
							Str::of($modelHeadline)->lower()
					)
					->setIcon(Icon::PLUS)
					->setLink($routeCreateLink),
			])
			->setQuery($query->orderByDesc("created_at"))
			->setRow(function (Model $model) use (
				$columnKeys,
				$routeEditLink,
				$routeDeleteLink,
				$modelRouteKeyName
			) {
				$tableRow = TableRow::make($model->{$model->getKeyName()});

				$values = collect($columnKeys)->map(function (
					string $columnKey
				) use ($model) {
					$value = $model->{$columnKey};

					return $value === null ? "–" : $value;
				});

				$routeKeyValue = $model->{$modelRouteKeyName};

				return $tableRow->setValues([
					...$values->all(),
					TableLinks::make()->setNodes([
						Link::make()
							->setTitle("Edit")
							->setLink(
								(string) Str::of($routeEditLink)->replace(
									"{model}",
									$routeKeyValue
								)
							),
						Link::make()
							->setTitle("Delete")
							->asDelete()
							->setAction(
								(string) Str::of($routeDeleteLink)->replace(
									"{model}",
									$routeKeyValue
								)
							),
					]),
				]);
			});

		collect($columnKeys)->each(
			fn(string $columnKey) => $paginatedTable->addColumn(
				Str::of($columnKey)
					->headline()
					->lower()
					->ucfirst()
			)
		);

		$paginatedTable->addLinksColumn();

		return $response->setNodes([$paginatedTable])->export();
	}
}
