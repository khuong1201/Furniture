import React from 'react';
import { Loader2 } from 'lucide-react';

const LoadingSpinner = () => (
    <div className="flex justify-center items-center p-12 text-gray-500">
        <Loader2 className="animate-spin mr-2" size={24} /> Loading...
    </div>
);
export default LoadingSpinner;