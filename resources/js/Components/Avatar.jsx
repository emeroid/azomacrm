// resources/js/Components/Avatar.jsx
export default function Avatar({ 
    name = '', 
    size = 'md',
    color = 'blue',
    src,
    className = '',
    rounded = 'full',
    showTooltip = false,
    tooltipPosition = 'bottom'
}) {
    // Size options
    const sizes = {
        xs: 'h-6 w-6 text-xs',
        sm: 'h-8 w-8 text-sm',
        md: 'h-10 w-10 text-base',
        lg: 'h-12 w-12 text-lg',
        xl: 'h-16 w-16 text-xl'
    };
    
    // Color options
    const colors = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        red: 'bg-red-500',
        yellow: 'bg-yellow-500',
        indigo: 'bg-indigo-500',
        purple: 'bg-purple-500',
        pink: 'bg-pink-500',
        gray: 'bg-gray-500',
        custom: ''
    };
    
    // Roundness options
    const borderRadius = {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        full: 'rounded-full'
    };
    
    // Tooltip positions
    const tooltipPositions = {
        top: 'tooltip-top',
        bottom: 'tooltip-bottom',
        left: 'tooltip-left',
        right: 'tooltip-right'
    };
    
    // Generate initials
    const getInitials = () => {
        if (!name) return '?';
        const parts = name.split(' ').filter(part => part.length > 0);
        if (parts.length === 0) return '?';
        if (parts.length === 1) return parts[0][0].toUpperCase();
        return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
    };
    
    // Render image avatar if src is provided
    if (src) {
        return (
            <div className={`relative ${sizes[size]} ${borderRadius[rounded]} ${className}`}>
                <img 
                    src={src} 
                    alt={name || 'Avatar'} 
                    className={`w-full h-full object-cover ${borderRadius[rounded]}`}
                    onError={(e) => {
                        e.target.style.display = 'none';
                        e.target.nextSibling.style.display = 'flex';
                    }}
                />
                <div 
                    className={`absolute inset-0 ${colors[color]} ${borderRadius[rounded]} flex items-center justify-center text-white font-medium ${sizes[size].split(' ')[1]}`}
                    style={{ display: 'none' }}
                >
                    {getInitials()}
                </div>
            </div>
        );
    }
    
    // Render initials avatar
    const avatarElement = (
        <div 
            className={`
                ${sizes[size]} 
                ${colors[color]} 
                ${borderRadius[rounded]} 
                flex items-center justify-center 
                text-white font-medium 
                select-none
                ${className}
            `}
        >
            {getInitials()}
        </div>
    );
    
    // Add tooltip if enabled
    if (showTooltip && name) {
        return (
            <div className={`tooltip ${tooltipPositions[tooltipPosition]}`} data-tip={name}>
                {avatarElement}
            </div>
        );
    }
    
    return avatarElement;
}