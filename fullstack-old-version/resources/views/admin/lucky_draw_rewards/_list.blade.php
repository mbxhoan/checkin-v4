@if (!empty($luckyDrawRewards) && $luckyDrawRewards->count())
    <input type="hidden" id="luckyDrawId" value="{{ $luckyDraw->id }}">
    <div class="row mt-2 text-xs border-bottom pb-2 mx-1">
        <div class="col-md-4 fw-bold">
            {{ __('lucky_draws.lucky_draw_rewards_list.th_info') }}
        </div>
        <div class="col-md-4 fw-bold">
            {{ __('lucky_draws.lucky_draw_rewards_list.th_assign') }}
        </div>
        <div class="col-md-2 fw-bold text-center">
            {{ __('lucky_draws.lucky_draw_rewards_list.th_image') }}
        </div>
        <div class="col-md-2 fw-bold text-center"></div>
    </div>
    @foreach ($luckyDrawRewards as $index => $luckyDrawReward)
        <div class="row align-items-center text-xs mt-2 {{ $luckyDrawReward->is_given }}">
            <div class="col-md-4">
                <div class="">
                    <span class="fw-bold">
                        {{ __('lucky_draws.lucky_draw_rewards_list.label_order_number') }}
                    </span>
                    {{ $luckyDrawReward->order }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        {{ __('lucky_draws.lucky_draw_rewards_list.label_order_name') }}
                    </span>
                    {{ $luckyDrawReward->order_name }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        {{ __('lucky_draws.lucky_draw_rewards_list.label_code') }}
                    </span>
                    {{ $luckyDrawReward->code }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        {{ __('lucky_draws.lucky_draw_rewards_list.label_name') }}
                    </span>
                    {{ $luckyDrawReward->name }}
                </div>
                <div class="">
                    <span class="fw-bold">
                        {{ __('lucky_draws.lucky_draw_rewards_list.label_winners_count') }}
                    </span>
                    {{ $luckyDrawReward->value }}
                </div>
            </div>
            <div class="col-md-4">
                <input type="hidden" id="data-assignee_id"
                    data-url="{{ route('admin.lucky_draw_rewards.update', $luckyDrawReward) }}"
                    data-reward_id="{{ $luckyDrawReward->id }}"
                >
                @php
                    if ($luckyDrawReward->is_given) {
                        $assignees = $luckyDrawClients->map(function ($client) {
                            return [
                                'id'        => $client->id,
                                'name'      => "{$client->qrcode} - {$client->name}",
                            ];
                        })->pluck('name', 'id')->toArray();
                    }
                @endphp
                @include('components.select', [
                    'id'            => 'assignee_id',
                    'fieldName'     => 'assignee_id',
                    'formClass'     => 'w-100 input-assignee_id',
                    'options'       => !empty($assignees) ? $assignees : [],
                    'selected'      => !empty($luckyDrawReward->assignee_id) ? $luckyDrawReward->assignee_id : '',
                ])
            </div>
            <div class="col-md-2 text-center">
                <img src="{{ $luckyDrawReward->img_link }}" class="" alt="{{ $luckyDrawReward->name }}"
                    style="
                        max-width: 100px;
                        /* width: ; */
                        height: 100px;
                    "
                >
            </div>
            <div class="col-md-2">
                @if ($luckyDrawReward->is_given)
                    <div class="mb-2">
                        <a id="btn-cancel-reward-{{ $luckyDrawReward->id }}"
                            class="btn-cancel-reward btn btn-xs btn-danger"
                            data-id="{{ $luckyDrawReward->id }}"
                            data-url="{{ route('admin.lucky-draw.cancel-reward') }}"
                            title="{{ __('lucky_draws.lucky_draw_rewards_list.action_cancel_reward_title') }}"
                        >
                            <i class="bx bx-x-circle"></i>
                        </a>
                    </div>
                @else
                    @include('admin.lucky_draw_rewards._modal-upsert', [
                        'model'                 => $luckyDrawReward,
                        'modalId'               => "updateRewardModal-{$luckyDrawReward->id}",
                        'text'                  => __('lucky_draws.lucky_draw_rewards_list.action_update_reward'),
                        'textIcon'              => '<i class="fa-solid fa-edit"></i>',
                        'route'                 => route('admin.lucky_draw_rewards.update', $luckyDrawReward),
                        'uploadedRewardImages'  => $luckyDraw->uploaded_reward_images ?? [],
                    ])
                @endif
                <form action="{{ route('admin.lucky_draw_rewards.destroy', $luckyDrawReward) }}"
                    class="form-inline"
                    method="POST"
                    onsubmit="return confirm('{{ __('lucky_draws.lucky_draw_rewards_list.confirm_delete_reward') }}');"
                >
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger btn-xs" title="{{ __('lucky_draws.lucky_draw_rewards_list.action_delete_reward_title') }}">
                        <x-icon name="trash" />
                    </button>
                </form>
            </div>
        </div>
    @endforeach
@else
    <div class="fst-italic pt-2">
        {{ __('lucky_draws.lucky_draw_rewards_list.empty_text') }}
    </div>
@endif
