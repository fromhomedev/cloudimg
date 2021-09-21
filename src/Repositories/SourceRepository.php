<?php

declare(strict_types=1);

namespace FromHome\Cloudimg\Repositories;

use ReflectionClass;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use FromHome\Cloudimg\Exceptions\SourceDoesNotExist;
use FromHome\Cloudimg\Contract\Models\SourceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use FromHome\Cloudimg\Contract\Repositories\SourceRepositoryInterface;

final class SourceRepository implements SourceRepositoryInterface
{
    private SourceInterface $source;

    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    public function findUsingId(int $id): ?SourceInterface
    {
        try {
            return $this->source->findById($id);
        } catch (SourceDoesNotExist $exception) {
            return null;
        }
    }

    public function findUsingDomain(string $domain): ?SourceInterface
    {
        try {
            return $this->source->findByDomain($domain);
        } catch (SourceDoesNotExist $exception) {
            return null;
        }
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->getModel()->newQuery()
            ->where('user_id', $request->user()->getKey())
            ->paginate((int) $request->query('limit'))
            ->appends((array) $request->query());
    }

    private function getModel(): Model
    {
        $classReflection = new ReflectionClass($this->source);

        if ($classReflection->isSubclassOf(Model::class)) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            /** @psalm-var Model */
            return $classReflection->newInstance();
        }

        throw new InvalidArgumentException(\sprintf('Class `%s` must be extend `%s`', \get_class($this->source), Model::class));
    }
}
