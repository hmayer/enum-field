<?php

namespace Hmayer\EnumField;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Validation\Rules\Enum as EnumRules;
use UnitEnum;

class Enum extends Select
{
    /**
     * @var array|EnumRules[]
     */
    private array $rules;

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
        $this->resolveUsing(
            function ($value) {
                return $value instanceof UnitEnum ? $value->value : $value;
            }
        );


        $this->fillUsing(
            function (NovaRequest $request, $model, $attribute, $requestAttribute) {
                if ($request->exists($requestAttribute)) {
                    $model->{$attribute} = $request[$requestAttribute];
                }
            }
        );
    }
    public function attach($class): static
    {
        $this->options(collect($class::cases())->pluck('name', 'value'));

        $this->displayUsing(
            function ($value) use ($class) {
                if ($value instanceof UnitEnum) {
                    return __($value->name);
                }

                $parsedValue = $class::tryFrom($value);
                if ($parsedValue instanceof UnitEnum) {
                    return __($parsedValue->name);
                }
                return __($value);
            }
        );

        $this->rules = [new EnumRules($class)];
        return $this;
    }
}
