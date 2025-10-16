import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

export function SortableItem({ id, children, active, onClick }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        borderColor: active ? '#6366f1' : '#e2e8f0',
        backgroundColor: active ? '#f5f3ff' : 'white',
        marginBottom: '0.75rem',
        borderRadius: '0.375rem',
        padding: '1rem',
        position: 'relative',
        cursor: 'pointer'
    };

    return (
        <div 
            ref={setNodeRef} 
            style={style} 
            {...attributes}
            onClick={onClick}
        >
            <div 
                {...listeners}
                className="absolute top-2 right-2 p-1 text-gray-400 hover:text-gray-600 cursor-move"
            >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                </svg>
            </div>
            {children}
        </div>
    );
}