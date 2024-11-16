<?php


class AIKIT_Gpt3TokenizerConfig
{

    private array $config = [
        'mergesPath' => __DIR__ . '/files/merges.txt',
        'vocabPath' => __DIR__ . '/files/vocab.json',
        'useCache' => true,
    ];

    public function mergesPath($path): AIKIT_Gpt3TokenizerConfig
    {
        $this->config['mergesPath'] = $path;
        return $this;
    }

    public function vocabPath($path): AIKIT_Gpt3TokenizerConfig
    {
        $this->config['vocabPath'] = $path;
        return $this;
    }

    public function useCache($useCache): AIKIT_Gpt3TokenizerConfig
    {
        $this->config['useCache'] = $useCache;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
