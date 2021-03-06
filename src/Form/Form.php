<?php

namespace SudoBee\Cygnus\Form;

use SudoBee\Cygnus\Component\Component;
use SudoBee\Cygnus\Component\Components\Table\Actions\GetTableSelectedValuesValidationRulesAction;
use SudoBee\Cygnus\Component\Traits\HasActions;
use SudoBee\Cygnus\Component\Traits\HasDescription;
use SudoBee\Cygnus\Component\Traits\HasNodes;
use SudoBee\Cygnus\Component\Traits\HasTitle;
use SudoBee\Cygnus\Component\Traits\HasWithPanel;
use SudoBee\Cygnus\Core\Utilities\ExportBuilder;
use SudoBee\Cygnus\Form\Actions\GetNestedFieldsDefaultValuesAction;
use SudoBee\Cygnus\Form\Actions\GetNestedFieldsValidationRulesAction;
use SudoBee\Cygnus\Form\Form\FormButton;
use Exception;

class Form extends Component
{
	use HasTitle;
	use HasDescription;
	use HasActions;
	use HasNodes;
	use HasWithPanel;

	/** @var array<string, mixed> $additionalValidationRules */
	private array $additionalValidationRules = [];

	/** @var array<string, mixed> $values */
	private array $values = [];

	private Operation $operation;

	/**
	 * @var FormButton[] $leftButtons
	 */
	private array $leftButtons = [];

	/**
	 * @var FormButton[] $rightButtons
	 */
	private array $rightButtons = [];

	private FormButton $defaultSubmitButton;

	/**
	 * @var Component[]
	 */
	private array $stickyHeader = [];

	private function __construct(Operation $operation)
	{
		$this->operation = $operation;

		$this->defaultSubmitButton = FormButton::make()->setTitle("Submit");
		$this->setRightButtons([$this->defaultSubmitButton]);
	}

	public static function make(Operation $operation): self
	{
		return new self($operation);
	}

	public function setSubmitButtonText(string $submitButtonText): self
	{
		$this->defaultSubmitButton->setTitle($submitButtonText);

		return $this;
	}

	/**
	 * @param array<string, mixed> $values
	 * @return $this
	 */
	public function setValues(array $values): self
	{
		$this->values = $values;

		return $this;
	}

	/**
	 * @param Component[] $stickyHeader
	 * @return $this
	 */
	public function setStickyHeader(array $stickyHeader): self
	{
		$this->stickyHeader = $stickyHeader;

		return $this;
	}

	/**
	 * @param FormButton[] $leftButtons
	 * @return $this
	 */
	public function setLeftButtons(array $leftButtons): self
	{
		$this->leftButtons = $leftButtons;

		return $this;
	}

	/**
	 * @param FormButton[] $rightButtons
	 * @return $this
	 */
	public function setRightButtons(array $rightButtons): self
	{
		$this->rightButtons = $rightButtons;

		return $this;
	}

	public function addLeftButtonOnLeft(FormButton $button): self
	{
		$this->setLeftButtons(array_merge([$button], $this->leftButtons));

		return $this;
	}

	public function addLeftButtonOnRight(FormButton $button): self
	{
		$this->setLeftButtons(array_merge($this->leftButtons, [$button]));

		return $this;
	}

	public function addRightButtonOnLeft(FormButton $button): self
	{
		$this->setRightButtons(array_merge([$button], $this->rightButtons));

		return $this;
	}

	public function addRightButtonOnRight(FormButton $button): self
	{
		$this->setRightButtons(array_merge($this->rightButtons, [$button]));

		return $this;
	}

	public function acceptsTableSelectedValues(string $model): self
	{
		$this->additionalValidationRules = array_merge(
			$this->additionalValidationRules,
			app(GetTableSelectedValuesValidationRulesAction::class)->execute(
				$model
			)
		);

		return $this;
	}

	/**
	 * @param object $formValues
	 * @return array<string, mixed>
	 */
	public function getValidationRules(object $formValues): array
	{
		$validationRulesFromFields = app(
			GetNestedFieldsValidationRulesAction::class
		)->execute($this->nodes, $formValues);

		return array_merge(
			$validationRulesFromFields,
			$this->additionalValidationRules
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getValues(): array
	{
		$defaultValues = app(
			GetNestedFieldsDefaultValuesAction::class
		)->execute($this->getNodes());

		$valuesWithoutNull = collect($this->values)
			->filter(fn(mixed $value) => $value !== null)
			->all();

		return array_merge($defaultValues, $valuesWithoutNull);
	}

	/**
	 * @return array<mixed>
	 * @throws Exception
	 */
	public function export(): array
	{
		$action = $this->operation->resolveRoute();
		$actionResponseType = $this->operation->getResponseType();

		return ExportBuilder::make($this)
			->mergeProperties($this->titleExport())
			->mergeProperties($this->descriptionExport())
			->mergeProperties($this->actionsExport())
			->mergeProperties($this->nodesExport())
			->mergeProperties($this->withPanelExport())
			->addNodesProperty("leftButtons", $this->leftButtons)
			->addNodesProperty("rightButtons", $this->rightButtons)
			->addNodesProperty("stickyHeader", $this->stickyHeader)
			->addProperty("action", $action)
			->addProperty("actionResponseType", $actionResponseType)
			->addProperty("values", $this->getValues())
			->export();
	}
}
