<a href="" class="btn btn-xs btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#{{ $modalId }}"
>
    {!! $textIcon !!}
    {{ $textBtn ?? null }}
</a>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{ $text }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="{{ $route }}"
                method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.code",
                            'fieldName'         => "reward[code]",
                            'placeholder'       => __('lucky_draws.modal-upsert.placeholder_code'),
                            'label'             => __('lucky_draws.modal-upsert.label_code'),
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'unique'            => true,
                            'required'          => true,
                            'value'             => $model->code ?? null,
                            'readonly'          => !empty($model->code) ? true : false,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.name",
                            'fieldName'         => "reward[name]",
                            'placeholder'       => __('lucky_draws.modal-upsert.placeholder_name'),
                            'label'             => __('lucky_draws.modal-upsert.label_name'),
                            'type'              => "text",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->name ?? null,
                        ])
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.value",
                            'fieldName'         => "reward[value]",
                            'placeholder'       => '1',
                            'label'             => __('lucky_draws.modal-upsert.label_winners_count'),
                            'type'              => "number",
                            'formClass'         => 'mb-3 col-md-6',
                            'required'          => true,
                            'value'             => $model->value ?? null,
                        ])
                        @php
                            $uploadedRewardImages = $uploadedRewardImages ?? [];
                            $currentImgLink = $model->img_link ?? null;
                            $imgLinkInList = $currentImgLink && collect($uploadedRewardImages)->contains(function ($item) use ($currentImgLink) {
                                $url = is_array($item) ? ($item['url'] ?? '') : $item;
                                return $url === $currentImgLink;
                            });
                        @endphp
                        <div class="mb-3 col-md-6">
                            <label class="form-label">{{ __('lucky_draws.modal-upsert.label_image_link') }} <span class="text-danger">*</span></label>
                            <select class="form-select reward-img-link-select" id="reward-img_link-select-{{ $modalId }}" data-modal-id="{{ $modalId }}" data-other-input-id="reward-img_link-other-{{ $modalId }}" data-hidden-id="reward-img_link-hidden-{{ $modalId }}">
                                <option value="">{{ __('lucky_draws.modal-upsert.select_uploaded_image') }}</option>
                                @foreach ($uploadedRewardImages as $item)
                                    @php
                                        $url = is_array($item) ? ($item['url'] ?? '') : $item;
                                        $name = is_array($item) ? ($item['name'] ?? $url) : ('Ảnh ' . $loop->iteration);
                                    @endphp
                                    <option value="{{ $url }}" {{ $currentImgLink === $url ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                                <option value="__other__" {{ !$imgLinkInList && $currentImgLink ? 'selected' : '' }}>{{ __('lucky_draws.modal-upsert.select_other_image') }}</option>
                            </select>
                            <input type="text" class="form-control mt-1 reward-img-link-other {{ !$imgLinkInList && $currentImgLink ? '' : 'd-none' }}" id="reward-img_link-other-{{ $modalId }}" placeholder="{{ __('lucky_draws.modal-upsert.placeholder_other_image') }}" value="{{ !$imgLinkInList && $currentImgLink ? $currentImgLink : '' }}">
                            <input type="hidden" name="reward[img_link]" id="reward-img_link-hidden-{{ $modalId }}" value="{{ $currentImgLink ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "reward.order",
                            'fieldName'         => "reward[order]",
                            'placeholder'       => 10,
                            'label'             => __('lucky_draws.modal-upsert.label_order'),
                            'type'              => "number",
                            'formClass'         => 'mb-3 col-md-6',
                            'unique'            => true,
                            'required'          => true,
                            'value'             => $model->order ?? null,
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                    <button type="submit" class="btn btn-danger">{{ __('lucky_draws.modal-upsert.action_save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
