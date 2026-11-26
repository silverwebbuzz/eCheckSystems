<div class="modal-onboarding modal fade animate__animated" id="onboardingHorizontalSlideModal" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content text-center">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="pricing-table">
                @if (!empty($packages) && count($packages) > 0)
                    @foreach ($packages as $package)
                        <div
                            class="pricing-card {{ $package->Name == 'PRO' || $package->Name == 'ENTERPRISE' ? 'popular' : '' }}{{ $user->CurrentPackageID != '-1' && $user->CurrentPackageID == $package->PackageID ? ' selected-plan' : '' }}">
                            <h3>{{ $package->Name }}</h3>
                            @if ($package->Duration < 30)
                                <p class="price">${{ $package->Price }} <span>({{ $package->Duration }} days)</span>
                                </p>
                            @else
                                <p class="price">${{ $package->Price }} <span>monthly</span></p>
                            @endif
                            <ul class="features">
                                @if ($package->Duration < 30)
                                    <li>Up to
                                        {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }}
                                        checks
                                        / {{ $package->Duration }} days</li>
                                @else
                                    <li>Up to
                                        {{ $package->Name != 'UNLIMITED' ? $package->CheckLimitPerMonth : 'Unlimited ' }}
                                        checks
                                        / month</li>
                                @endif
                                <li>Email Support</li>
                                <li>Unlimited Users</li>
                                @if ($package->Name != 'BASIC')
                                    <li>Custom Webform*</li>
                                @endif
                                <li>3 mos History Storage</li>
                            </ul>
                            @if ($user->CurrentPackageID != '-1' && $user->CurrentPackageID == $package->PackageID)
                                <p class="current-plan">Current Plan</p>
                            @else
                                @if ($user->CurrentPackageID != '-1')
                                    <a href="{{ route('user.select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                        class="plan-button" onclick="$('#payment-loader').show();">Select Plan</a>
                                @else
                                    <a href="{{ route('user-select-package', ['id' => $user->UserID, 'plan' => $package->PackageID]) }}"
                                        class="plan-button" onclick="$('#payment-loader').show();">Select Plan</a>
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
