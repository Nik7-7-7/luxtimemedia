<?php
/**
 * RecommendRequest
 *
 * @since     Jun 2023
 * @author    Greg Priday <greg@siteorigin.com>
 */
namespace AIKit\Dependencies\Qdrant\Models\Request;

use AIKit\Dependencies\Qdrant\Models\Filter\Filter;
use AIKit\Dependencies\Qdrant\Models\Traits\ProtectedPropertyAccessor;

class RecommendRequest
{
    use ProtectedPropertyAccessor;

    protected ?Filter $filter = null;
    protected array $positive;
    protected array $negative;
    protected ?string $using = null;
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected ?float $scoreThreshold = null;

    public function __construct(array $positive, array $negative = [])
    {
        $this->positive = $positive;
        $this->negative = $negative;
    }

    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function setScoreThreshold(float $scoreThreshold)
    {
        $this->scoreThreshold = $scoreThreshold;

        return $this;
    }

    public function setUsing(string $using)
    {
        $this->using = $using;

        return $this;
    }

    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function toArray(): array
    {
        $body = [
            'positive' => $this->positive,
            'negative' => $this->negative,
        ];

        if ($this->filter !== null && $this->filter->toArray()) {
            $body['filter'] = $this->filter->toArray();
        }
        if($this->scoreThreshold) {
            $body['score_threshold'] = $this->scoreThreshold;
        }
        if ($this->using) {
            $body['using'] = $this->using;
        }
        if ($this->limit) {
            $body['limit'] = $this->limit;
        }
        if ($this->offset) {
            $body['offset'] = $this->offset;
        }

        return $body;
    }
}