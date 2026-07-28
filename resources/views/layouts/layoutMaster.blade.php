@isset($pageConfigs)
    {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
    use Illuminate\Support\Facades\Route;
    $configData = Helper::appClasses();
    if (
        Route::is('user.register') ||
        Route::is('user.login') ||
        Route::is('user.forgot-password') ||
        Route::is('user.showResetForm')
    ) {
        $configData['layout'] = 'blank';
    }
@endphp

@isset($configData['layout'])
    @include(
        $configData['layout'] === 'horizontal'
            ? 'layouts.horizontalLayout'
            : ($configData['layout'] === 'blank'
                ? 'layouts.blankLayout'
                : ($configData['layout'] === 'front'
                    ? 'layouts.layoutFront'
                    : 'layouts.contentNavbarLayout')))
@endisset
