<?php

namespace Apiato\Core\Generator\Traits;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multisearch;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

trait ConsoleInputTrait
{
    protected function checkParameterOrAskText(string $param, string $label, string|null $default = '', string $hint = '', string $placeHolder = '', bool $required = true): string
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = text(
                label: $label,
                placeholder: $placeHolder,
                default: $default,
                required: $required,
                hint: $hint,
            );
        }

        return $value;
    }

    protected function checkParameterOrAskTextSuggested(string $param, string $label, string|null $default = '', array $suggestions = [], string $hint = '', string $placeHolder = '', bool $required = true): string
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = suggest(
                label: $label,
                options: $suggestions,
                placeholder: $placeHolder,
                default: $default,
                required: $required,
                hint: $hint,
            );
        }

        return $value;
    }

    protected function checkParameterOrAskTextArea(string $param, string $label, int $rows = 5, string|null $default = '', string $hint = '', string $placeHolder = '', bool $required = true): string
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = textarea(
                label: $label,
                placeholder: $placeHolder,
                default: $default,
                required: $required,
                hint: $hint,
                rows: $rows,
            );
        }

        return $value;
    }

    protected function checkParameterOrAskPassword(string $param, string $label, string $hint = '', string $placeHolder = '', bool $required = true): string
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = password(
                label: $label,
                placeholder: $placeHolder,
                required: $required,
                hint: $hint,
            );
        }

        return $value;
    }

    protected function checkParameterOrSelect(string $param, string $label, array $options, string|null $default = null, string $hint = '', bool $required = true): string
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = select(
                label: $label,
                options: $options,
                default: $default,
                hint: $hint,
                required: $required,
            );
        }

        return $value;
    }

    protected function checkParameterOrMultiSelect(string $param, string $label, array $options, int $scroll = 10, array|null $default = null, string $hint = '', bool $required = true): array
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = multiselect(
                label: $label,
                options: $options,
                default: $default,
                scroll: $scroll,
                required: $required,
                hint: $hint,
            );
        }

        return $value;
    }

    protected function checkParameterOrSearchableSelect(string $param, string $label, \Closure $options, int $scroll = 10, string $hint = '', string $placeHolder = '', bool $required = true): string
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = search(
                label: $label,
                options: $options,
                placeholder: $placeHolder,
                scroll: $scroll,
                hint: $hint,
                required: $required,
            );
        }

        return $value;
    }

    protected function checkParameterOrMultiSearchableSelect(string $param, string $label, \Closure $options, int $scroll = 10, string $hint = '', string $placeHolder = '', bool $required = true): array
    {
        // Check if we already have the param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user for the param
            $value = multisearch(
                label: $label,
                options: $options,
                placeholder: $placeHolder,
                scroll: $scroll,
                required: $required,
                hint: $hint,
            );
        }

        return $value;
    }

    protected function checkParameterOrConfirm(string $param, string $label, bool $default = true, string $yes = 'Yes', string $no = 'No', string $hint = '', bool $required = true): bool
    {
        // Check if we already have a param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user...
            $value = confirm(
                label: $label,
                default: $default,
                yes: $yes,
                no: $no,
                required: $required,
                hint: $hint,
            );
        }

        return $value;
    }

    protected function checkParameterOrPause(string $param, string $message): bool
    {
        // Check if we already have a param set
        $value = $this->option($param);
        if (null === $value) {
            // There was no value provided via CLI, so ask the user...
            $value = pause(
                message: $message,
            );
        }

        return $value;
    }
}
