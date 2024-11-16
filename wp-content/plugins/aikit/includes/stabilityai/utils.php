<?php

function aikit_stability_ai_get_model_allowed_resolutions ($model) {
    if ($model === 'stable-diffusion-xl-1024-v0-9' || $model === 'stable-diffusion-xl-1024-v1-0') {
        return [
            'large' => '1024x1024',
        ];
    } else {
        return [
            'medium' => '512x512',
            'large' => '1024x1024',
            'xlarge landscape ' => '1344x768',
        ];
    }
}

function aikit_openai_get_model_allowed_resolutions () {
    $image_model = get_option('aikit_setting_openai_image_model') ?? 'dall-e-2';

    if ($image_model === 'dall-e-2') {
        return [
            'small' => '256x256',
            'medium' => '512x512',
            'large' => '1024x1024',
        ];
    } else if ($image_model === 'dall-e-3') {
        return [
            'large' => '1024x1024',
            'xlarge landscape' => '1792x1024',
            'xlarge portrait' => '1024x1792',
        ];
    }

    return [];
}

function aikit_openai_get_model_allowed_image_generation_counts ()
{
    $image_model = get_option('aikit_setting_openai_image_model') ?? 'dall-e-2';

    if ($image_model === 'dall-e-3') {
        return [1];
    }

    return explode(',', get_option('aikit_setting_images_counts'));
}