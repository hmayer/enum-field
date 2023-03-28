<?php

namespace Hmayer\EnumField;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Validation\Rules\Enum as EnumRules;
use UnitEnum;


/**
 * @property $rules EnumRules[]
 */
class Enum extends Select
{
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
        $this->options(
            collect($class::cases())
                ->pluck('name', 'value')
                ->map(function ($option) {
                    return __($option);
                })
        );

        $this->displayUsing(
            function ($value) use ($class) {
                if ($value instanceof UnitEnum) {
                    return $value->name;
                }

                $parsedValue = $class::tryFrom($value);
                if ($parsedValue instanceof UnitEnum) {
                    return $parsedValue->name;
                }
                return $value;
            }
        );

        $this->rules = [new EnumRules($class)];
        return $this;
    }
}
