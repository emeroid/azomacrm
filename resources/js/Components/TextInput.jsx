// resources/js/Components/TextInput.jsx
export default function TextInput({ className = '', ...props }) {
    return (
        <input
            {...props}
            className={`rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ${className}`}
        />
    );
}