<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\SelectPrompt;

class SelectPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the select prompt.
     */
    public function __invoke(SelectPrompt $prompt): string
    {
        $maxWidth = $prompt->terminal()->cols() - 6;

        return match ($prompt->state) {
            'submit' => $this
                ->box(
                    $this->dim($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->dim($this->truncate($this->format($prompt->label()), $maxWidth)),
                ),

            'cancel' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderOptions($prompt),
                    color: 'red',
                )
                ->error('Cancelled.'),

            'error' => $this
                ->box(
                    $this->truncate($prompt->label, $prompt->terminal()->cols() - 6),
                    $this->renderOptions($prompt),
                    color: 'yellow',
                )
                ->warning($prompt->error),

            default => $this
                ->box(
                    $this->cyan($this->truncate($prompt->label, $prompt->terminal()->cols() - 6)),
                    $this->renderOptions($prompt),
                )
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(SelectPrompt $prompt): string
    {
        return $this->scroll(
            collect($prompt->options)
                ->values()
                ->map(fn ($label) => $this->truncate($this->format($label), $prompt->terminal()->cols() - 12))
                ->map(fn ($label, $i) => $prompt->highlighted === $i
                    ? "{$this->cyan('›')} {$this->cyan('●')} {$label}  "
                    : "  {$this->dim('○')} {$this->dim($label)}  "
                ),
            $prompt->highlighted,
            min($prompt->scroll, $prompt->terminal()->lines() - 5),
            min($this->longest($prompt->options, padding: 6), $prompt->terminal()->cols() - 6)
        )->implode(PHP_EOL);
    }
}
