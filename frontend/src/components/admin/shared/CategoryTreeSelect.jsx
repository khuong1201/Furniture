import React from 'react';

const CategoryTreeSelect = ({ treeData, value, onChange, placeholder = "Select Parent Category", currentId = null }) => {
    
    const renderOptions = (categories, level = 0) => {
        return categories.map(cat => {
            if (currentId && cat.id === currentId) return null;

            return (
                <React.Fragment key={cat.uuid}>
                    <option value={cat.id}>
                        {/* Tạo thụt đầu dòng bằng ký tự unicode */}
                        {level === 0 ? '' : '└─ '.padStart(level * 3 + 3, '\u00A0')}
                        {cat.name}
                    </option>
                    {cat.children && cat.children.length > 0 && renderOptions(cat.children, level + 1)}
                </React.Fragment>
            );
        });
    };

    return (
        <div className="form-group">
            <label className="form-label">Parent Category</label>
            <div className="select-wrapper">
                <select 
                    className="form-select"
                    value={value || ''} 
                    onChange={(e) => onChange(e.target.value)}
                >
                    <option value="">{placeholder}</option>
                    {renderOptions(treeData)}
                </select>
            </div>
            <p className="helper-text">Leave empty if this is a root category.</p>
        </div>
    );
};

export default CategoryTreeSelect;