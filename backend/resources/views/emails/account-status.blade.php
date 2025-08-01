<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: #141F78;">
        Account Status Update
    </h2>
    
    <div style="color: #374151; margin-bottom: 1.5rem;">
        <p style="margin-bottom: 1rem;">
            Your account status has been updated to 
            <strong style="color: #141F78;">{{ ucfirst($status) }}</strong>.
        </p>
        
        @if($actionText && $actionUrl)
            <div style="margin: 1.5rem 0;">
                <a href="{{ $actionUrl }}" 
                   style="display: inline-block; padding: 12px 24px; 
                          background-color: #F25100; color: #FFFFFF; 
                          text-decoration: none; border-radius: 6px; 
                          font-weight: 600;">
                    {{ $actionText }}
                </a>
            </div>
        @endif
    </div>

    <div style="margin-top: 2rem; font-size: 0.875rem; color: #6B7280;">
        <p>If you believe this change was made in error, please contact our support team.</p>
    </div>
</div> 