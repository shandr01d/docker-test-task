<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\TaskDto;
use App\Dto\TaskUpdateDto;
use App\Entity\Task;
use App\Services\TaskBuilderInterface;
use App\Services\TaskUpdaterInterface;

final class TaskDtoDataTransformer implements DataTransformerInterface
{
    private $validator;
    private $taskBuilder;
    private $taskUpdater;

    public function __construct(ValidatorInterface $validator, TaskBuilderInterface $taskBuilder, TaskUpdaterInterface $taskUpdater)
    {
        $this->validator = $validator;
        $this->taskBuilder = $taskBuilder;
        $this->taskUpdater = $taskUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data, string $to, array $context = [])
    {
        $this->validator->validate($data);

        if(isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE])) {
            $existingTask = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];
            /** @var TaskUpdateDto $data */
            return $this->taskUpdater->update($existingTask, $data);
        }

        /** @var TaskDto $data */
        return $this->taskBuilder->build($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // in the case of an input, the value given here is an array (the JSON decoded).
        // if it's a task we transformed the data already
        if ($data instanceof Task) {
            return false;
        }

        return Task::class === $to && null !== ($context['input']['class'] ?? null);
    }
}