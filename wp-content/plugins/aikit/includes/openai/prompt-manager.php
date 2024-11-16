<?php

class AIKit_Prompt_Manager {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKit_Prompt_Manager();
        }
        return self::$instance;
    }

    public function build_prompts_by_language (array $allPrompts)
    {
        $promptsByLang = array();
        foreach ($allPrompts as $operationId => $obj) {
            foreach ($obj['languages'] as $lang => $langObj) {
                if (!empty($langObj['menuTitle']) && !empty($langObj['prompt'])) {
                    $promptsByLang[$lang][$operationId] = array(
                        'menuTitle' => $langObj['menuTitle'],
                        'prompt' => $langObj['prompt'],
                        'wordLength' => $obj['wordLength'],
                        'requiresTextSelection' => $obj['requiresTextSelection'],
                        'icon' => $obj['icon'],
                        'generatedTextPlacement' => $obj['generatedTextPlacement'],
                        'temperature' => $obj['temperature'],
                    );
                }
            }
        }

        return $promptsByLang;
    }

    public function get_all_prompts ()
    {
        $prompts = get_option('aikit_prompts');

        if (!$prompts) {
            $prompts = AIKIT_INITIAL_PROMPTS;
        }

        return $prompts;
    }

    public function get_prompts_by_language ( string $lang )
    {
        $prompts = get_option('aikit_prompts_' . $lang);

        if (!$prompts) {
            $languagePromptsMap = $this->build_prompts_by_language(
                AIKIT_INITIAL_PROMPTS
            );

            $prompts = $languagePromptsMap[$lang];
        }

        return $prompts;
    }

    public function get_prompts_for_frontend (string $lang)
    {
        $prompts = get_option('aikit_prompts_' . $lang);

        if (!$prompts) {
            $languagePromptsMap = $this->build_prompts_by_language(
                AIKIT_INITIAL_PROMPTS
            );

            $prompts = $languagePromptsMap[$lang];
        }

        foreach ($prompts as $operationId => $obj) {
            unset($prompts[$operationId]['prompt']);
            unset($prompts[$operationId]['wordLength']);
            unset($prompts[$operationId]['temperature']);
        }

        return $prompts;
    }
}
