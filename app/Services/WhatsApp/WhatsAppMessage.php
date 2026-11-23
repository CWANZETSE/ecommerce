<?php

namespace App\Services\WhatsApp;

use InvalidArgumentException;

class WhatsAppMessage
{
    /**
     * WhatsApp message type: text or template.
     */
    protected string $type;

    /**
     * Text message content.
     */
    protected ?string $content = null;

    /**
     * Whether to enable URL previews in text messages.
     */
    protected bool $previewUrl = false;

    /**
     * Approved WhatsApp template name.
     */
    protected ?string $templateName = null;

    /**
     * Template language code.
     */
    protected ?string $language = null;

    /**
     * Template components.
     *
     * Supports header, body, button and other supported
     * WhatsApp template components.
     */
    protected array $components = [];

    protected function __construct(string $type)
    {
        $this->type = $type;
    }

    /*
    |--------------------------------------------------------------------------
    | Text Messages
    |--------------------------------------------------------------------------
    */

    /**
     * Create a plain text WhatsApp message.
     */
    public static function text(string $content): self
    {
        $content = trim($content);

        if ($content === '') {
            throw new InvalidArgumentException(
                'WhatsApp text message cannot be empty.'
            );
        }

        $message = new self('text');

        $message->content = $content;

        return $message;
    }

    /**
     * Enable or disable URL previews for text messages.
     */
    public function previewUrl(bool $enabled = true): self
    {
        $this->previewUrl = $enabled;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Template Messages
    |--------------------------------------------------------------------------
    */

    /**
     * Create an approved WhatsApp template message.
     */
    public static function template(
        string $name,
        string $language = 'en',
    ): self {
        $name = trim($name);
        $language = trim($language);

        if ($name === '') {
            throw new InvalidArgumentException(
                'WhatsApp template name cannot be empty.'
            );
        }

        if ($language === '') {
            throw new InvalidArgumentException(
                'WhatsApp template language cannot be empty.'
            );
        }

        $message = new self('template');

        $message->templateName = $name;
        $message->language = $language;

        return $message;
    }

    /**
     * Set the template language.
     */
    public function language(string $language): self
    {
        $language = trim($language);

        if ($language === '') {
            throw new InvalidArgumentException(
                'WhatsApp template language cannot be empty.'
            );
        }

        $this->language = $language;

        return $this;
    }

    /**
     * Add body parameters to the template.
     *
     * Values are converted into WhatsApp text parameters.
     */
    public function bodyParameters(array $values): self
    {
        return $this->addComponent([
            'type' => 'body',
            'parameters' => $this->textParameters($values),
        ]);
    }

    /**
     * Add header parameters to the template.
     *
     * Accepts raw WhatsApp parameter objects to support
     * text, image, video, document and other media types.
     */
    public function headerParameters(array $parameters): self
    {
        return $this->addComponent([
            'type' => 'header',
            'parameters' => $parameters,
        ]);
    }

    /**
     * Add button parameters to the template.
     */
    public function buttonParameters(
        int $index,
        string $subType,
        array $parameters,
    ): self {
        if ($index < 0) {
            throw new InvalidArgumentException(
                'WhatsApp button index cannot be negative.'
            );
        }

        $subType = strtolower(trim($subType));

        if ($subType === '') {
            throw new InvalidArgumentException(
                'WhatsApp button subtype cannot be empty.'
            );
        }

        return $this->addComponent([
            'type' => 'button',
            'sub_type' => $subType,
            'index' => (string) $index,
            'parameters' => $parameters,
        ]);
    }

    /**
     * Add a custom template component.
     *
     * This allows future support for additional Meta
     * template component types without changing this DTO.
     */
    public function addComponent(array $component): self
    {
        if (empty($component['type'])) {
            throw new InvalidArgumentException(
                'WhatsApp template component must have a type.'
            );
        }

        $this->components[] = $component;

        return $this;
    }

    /**
     * Add multiple raw WhatsApp template components.
     */
    public function components(array $components): self
    {
        foreach ($components as $component) {
            if (!is_array($component)) {
                throw new InvalidArgumentException(
                    'Each WhatsApp template component must be an array.'
                );
            }

            $this->addComponent($component);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Convert simple values into WhatsApp text parameters.
     */
    protected function textParameters(array $values): array
    {
        return array_map(
            static fn (mixed $value): array => [
                'type' => 'text',
                'text' => (string) $value,
            ],
            array_values($values)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Message Information
    |--------------------------------------------------------------------------
    */

    public function isText(): bool
    {
        return $this->type === 'text';
    }

    public function isTemplate(): bool
    {
        return $this->type === 'template';
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTemplateName(): ?string
{
    return $this->templateName;
}

    /*
    |--------------------------------------------------------------------------
    | Meta API Payload
    |--------------------------------------------------------------------------
    */

    /**
     * Convert the message into a WhatsApp Cloud API payload.
     */
    public function toArray(): array
    {
        if ($this->isText()) {
            return [
                'type' => 'text',
                'text' => [
                    'preview_url' => $this->previewUrl,
                    'body' => $this->content,
                ],
            ];
        }

        if ($this->isTemplate()) {
            if (empty($this->templateName) || empty($this->language)) {
                throw new InvalidArgumentException(
                    'WhatsApp template name and language are required.'
                );
            }

            $template = [
                'name' => $this->templateName,
                'language' => [
                    'code' => $this->language,
                ],
            ];

            if (!empty($this->components)) {
                $template['components'] = $this->components;
            }

            return [
                'type' => 'template',
                'template' => $template,
            ];
        }

        throw new InvalidArgumentException(
            'Unsupported WhatsApp message type.'
        );
    }
}
