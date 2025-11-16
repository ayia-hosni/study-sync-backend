<?php

namespace App\GraphQL\Validators\Post;

use Nuwave\Lighthouse\Validation\Validator;

final class CreatePostCommentValidator extends Validator
{
    /**
     * Return the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Important: since fields are under "input", use dot notation
        return [
            'input.post_id' => ['required', 'exists:posts,id'],
            'input.content' => ['required', 'string', 'max:5000'],
            'input.media_urls' => ['nullable', 'array'],
            'input.media_urls.*' => ['url'],
            'input.parent_id' => ['nullable', 'exists:post_comments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'input.post_id.required' => 'The post id field is required.',
            'input.content.required' => 'The content field is required.',
        ];
    }
}
