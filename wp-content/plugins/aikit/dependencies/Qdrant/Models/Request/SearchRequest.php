<?php
/**
 * SearchParams
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace AIKit\Dependencies\Qdrant\Models\Request;

use AIKit\Dependencies\Qdrant\Models\Filter\Filter;
use AIKit\Dependencies\Qdrant\Models\Traits\ProtectedPropertyAccessor;
use AIKit\Dependencies\Qdrant\Models\VectorStruct;

class SearchRequest
{
    use ProtectedPropertyAccessor;

    protected ?Filter $filter = null;

    protected array $params = [];

    protected VectorStruct $vector;

    protected ?int $limit = null;

    protected ?int $offset = null;

    protected $withVector = null;

    protected $withPayload = null;

    protected ?float $scoreThreshold = null;

    public function __construct(VectorStruct $vector)
    {
        $this->vector = $vector;
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

    public function setParams(array $params)
    {
        $this->params = $params;

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

    public function setWithPayload($withPayload)
    {
        $this->withPayload = $withPayload;

        return $this;
    }

    public function setWithVector($withVector)
    {
        $this->withVector = $withVector;

        return $this;
    }

    public function toArray(): array
    {
        $body = [
            'vector' => $this->vector->toSearch(),
        ];
        if ($this->filter !== null && $this->filter->toArray()) {
            $body['filter'] = $this->filter->toArray();
        }
        if($this->scoreThreshold) {
            $body['score_threshold'] = $this->scoreThreshold;
        }
        if ($this->params) {
            $body['params'] = $this->params;
        }
        if ($this->limit) {
            $body['limit'] = $this->limit;
        }
        if ($this->offset) {
            $body['offset'] = $this->offset;
        }
        if ($this->withVector) {
            $body['with_vector'] = $this->withVector;
        }
        if ($this->withPayload) {
            $body['with_payload'] = $this->withPayload;
        }

        return $body;
    }

}