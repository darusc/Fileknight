<?php

namespace Fileknight\Controller\Traits;

use Symfony\Component\HttpFoundation\Request;

trait RequestJsonGetterTrait
{
    private const DECODED_JSON_ATTR = '_decoded_json';

    private function getJsonContent(Request $request): array
    {
        if ($request->attributes->has(self::DECODED_JSON_ATTR)) {
            return $request->attributes->get(self::DECODED_JSON_ATTR);
        }

        $content = json_decode($request->getContent(), true);
        $request->attributes->set(self::DECODED_JSON_ATTR, $content);

        return $content;
    }

    private function getJsonField(Request $request, string $key, mixed $default = null): mixed
    {
        $content = $this->getJsonContent($request);
        return $content[$key] ?? $default;
    }
}
