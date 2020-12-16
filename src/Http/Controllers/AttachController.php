<?php

namespace NovaAttachMany\Http\Controllers;

use Laravel\Nova\Resource;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttachController extends Controller
{
    public function create(NovaRequest $request, $parent, $relationship)
    {
        return [
            'available' => $this->getAvailableResources($request, $relationship),
        ];
    }

    public function edit(NovaRequest $request, $parent, $parentId, $relationship)
    {
        return [
            'selected' => $request->findResourceOrFail()->model()->{$relationship}->pluck('id'),
            'available' => $this->getAvailableResources($request, $relationship),
        ];
    }

    public function getAvailableResources($request, $relationship)
    {
        $resourceClass = $request->newResource();

        $field = $resourceClass
            ->availableFields($request)
            ->where('component', 'nova-attach-many')
            ->where('attribute', $relationship)
            ->first();

        $query = $field->resourceClass::newModel();

        return $field->resourceClass::relatableQuery($request, $query)
            ->cursor()
            ->mapInto($field->resourceClass)
            ->filter(function ($resource) use ($request, $field) {
                return $request->newResource()->authorizedToAttach($request, $resource->resource);
            })
            ->map(function ($resource) use ($field) {
                return [
                    'display' => $resource->title(),
                    'value' => $resource->getKey(),
                    'previewImg' => $resource->previewImg ?? null,
                    'novaUrl' => $this->buildNovaResourceUrl($resource, $field),
                    'detailAttribute' => optional($resource)->{$field->detailAttribute},
                ];
            })
            ->sortBy('display')
            ->values();
    }

    public function buildNovaResourceUrl(Resource $resource, $field)
    {
        return url(
            vsprintf('%s/resources/%s/%d', [
                config('nova.path'),
                $field->resourceClass::uriKey(),
                $resource->getKey(),
            ])
        );
    }
}
