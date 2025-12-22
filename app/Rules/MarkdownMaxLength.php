<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MarkdownMaxLength implements ValidationRule
{
    protected int $maxLength;

    public function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If value is null or empty, skip validation (handled by nullable/required rules)
        if (is_null($value) || $value === '') {
            return;
        }

        // If value is not a string, fail
        if (!is_string($value)) {
            $fail("The {$attribute} must be a string.");
            return;
        }

        // Try to parse as JSON (Lexical editor state)
        try {
            $editorState = json_decode($value, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If not valid JSON, treat as plain text
                $textContent = $value;
            } else {
                // Extract text content from Lexical editor state
                $textContent = $this->extractTextFromEditorState($editorState);
            }
        } catch (\Exception $e) {
            // If any error occurs, treat as plain text
            $textContent = $value;
        }

        // Validate text content length
        $textLength = mb_strlen($textContent);
        
        if ($textLength > $this->maxLength) {
            $fail("The {$attribute} content must not exceed {$this->maxLength} characters. Current length: {$textLength} characters.");
        }
    }

    /**
     * Recursively extract text content from Lexical editor state
     */
    private function extractTextFromEditorState(array $editorState): string
    {
        $text = '';

        // Check if this is a text node
        if (isset($editorState['text'])) {
            return $editorState['text'];
        }

        // If it has children, recursively extract text from them
        if (isset($editorState['children']) && is_array($editorState['children'])) {
            foreach ($editorState['children'] as $child) {
                if (is_array($child)) {
                    $text .= $this->extractTextFromEditorState($child);
                    
                    // Add space between block elements (paragraphs, headings, etc.)
                    if (isset($child['type']) && in_array($child['type'], ['paragraph', 'heading', 'list', 'listitem'])) {
                        $text .= ' ';
                    }
                }
            }
        }

        // Check root node
        if (isset($editorState['root']) && is_array($editorState['root'])) {
            $text .= $this->extractTextFromEditorState($editorState['root']);
        }

        return $text;
    }
}
