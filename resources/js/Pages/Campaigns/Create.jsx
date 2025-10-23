// resources/js/Pages/Campaigns/Create.jsx
import React, { useState, useRef } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/WaAuthLayout';

export default function CreateCampaign({ auth, devices, recentCampaigns }) {
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    const [mediaPreview, setMediaPreview] = useState(null);
    const fileInputRef = useRef(null);
    const textareaRef = useRef(null);

    const { data, setData, post, processing, errors, progress } = useForm({
        session_id: devices.length > 0 ? devices[0].session_id : '',
        input_method: 'manual',
        phone_numbers: '',
        contacts_file: null,
        message: '',
        media_file: null,
    });

    function handleSubmit(e) {
        e.preventDefault();
        post(route('campaigns.store', { sessionId: data.session_id }), {
            forceFormData: true,
        });
    }

    // Emoji data
    const emojiCategories = [
        {
            name: 'Smileys & People',
            emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳']
        },
        {
            name: 'Objects & Symbols',
            emojis: ['💯', '✨', '🌟', '⭐', '🔥', '💥', '🎉', '🎊', '🎁', '🏆', '🥇', '💰', '💎', '📱', '📞', '📧', '📨', '✉️', '📩', '📤', '📥', '🔔', '📯', '🎵', '🎶', '📢', '🔊', '📣', '🔍', '🔎']
        },
        {
            name: 'Travel & Places',
            emojis: ['🚀', '✈️', '🛩️', '🚁', '🚂', '🚄', '🚅', '🚆', '🚇', '🚈', '🚉', '🚊', '🚝', '🚞', '🚋', '🚌', '🚍', '🚎', '🚐', '🚑', '🚒', '🚓', '🚔', '🚕', '🚖', '🚗', '🚘', '🚙', '🚚', '🚛']
        },
        {
            name: 'Activities',
            emojis: ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🎿', '⛷️', '🏂', '🪂', '🏋️', '🤼', '🤸', '⛹️', '🤾', '🏌️', '🏇', '🧘', '🏄']
        }
    ];

    const insertEmoji = (emoji) => {
        const textarea = textareaRef.current;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const newMessage = data.message.substring(0, start) + emoji + data.message.substring(end);
        
        setData('message', newMessage);
        setShowEmojiPicker(false);
        
        // Focus back to textarea and set cursor position
        setTimeout(() => {
            textarea.focus();
            textarea.setSelectionRange(start + emoji.length, start + emoji.length);
        }, 0);
    };

    const handleMediaChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('media_file', file);
            
            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                setMediaPreview({
                    url: e.target.result,
                    type: file.type,
                    name: file.name
                });
            };
            reader.readAsDataURL(file);
        }
    };

    const removeMedia = () => {
        setData('media_file', null);
        setMediaPreview(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    // Calculate campaign statistics
    const getCampaignStats = (campaign) => {
        const successRate = campaign.total_recipients > 0 
            ? ((campaign.sent_count / campaign.total_recipients) * 100).toFixed(1)
            : 0;
        
        return {
            successRate,
            pending: campaign.total_recipients - campaign.sent_count - campaign.failed_count,
        };
    };

    const getStatusBadge = (campaign) => {
        const now = new Date();
        const queuedAt = new Date(campaign.queued_at);
        const isRecent = (now - queuedAt) < 5 * 60 * 1000;
        
        if (campaign.sent_count + campaign.failed_count >= campaign.total_recipients) {
            return { text: 'COMPLETED', class: 'bg-green-100 text-green-800' };
        } else if (campaign.sent_count > 0 || campaign.failed_count > 0) {
            return { text: 'IN PROGRESS', class: 'bg-blue-100 text-blue-800' };
        } else if (isRecent) {
            return { text: 'QUEUED', class: 'bg-yellow-100 text-yellow-800' };
        } else {
            return { text: 'STALLED', class: 'bg-red-100 text-red-800' };
        }
    };

    const recipientCount = data.input_method === 'manual' 
        ? data.phone_numbers.split('\n').filter(n => n.trim()).length 
        : data.contacts_file ? 'From file' : 0;

    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 className="font-semibold text-2xl text-gray-800 leading-tight">Create Broadcast Campaign 📢</h2>
                        <p className="text-gray-600 mt-1">Send bulk WhatsApp messages to your audience</p>
                    </div>
                    <div className="flex space-x-3 mt-4 sm:mt-0">
                        <Link 
                            href={route('campaigns.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View History
                        </Link>
                        <Link 
                            href={route('analytics.index')} 
                            className="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            View Analytics
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Create Campaign" />
            
            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Main Form */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                                <div className="px-8 py-6 border-b border-gray-200">
                                    <div className="flex items-center space-x-3">
                                        <div className="p-2 bg-indigo-100 rounded-lg">
                                            <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-bold text-gray-900">New Campaign</h3>
                                            <p className="text-gray-600">Fill in the details below to launch your broadcast</p>
                                        </div>
                                    </div>
                                </div>

                                <form onSubmit={handleSubmit} className="p-8 space-y-8">
                                    {/* Device Selection */}
                                    <div>
                                        <label htmlFor="session_id" className="block text-sm font-medium text-gray-700 mb-2">
                                            Sending Device *
                                        </label>
                                        <select
                                            id="session_id"
                                            value={data.session_id}
                                            onChange={(e) => setData('session_id', e.target.value)}
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                            disabled={devices.length === 0}
                                        >
                                            {devices.length > 0 ? (
                                                devices.map(device => (
                                                    <option key={device.id} value={device.session_id}>
                                                        {device.name} ({device.session_id})
                                                    </option>
                                                ))
                                            ) : (
                                                <option>No devices connected. Please add one first.</option>
                                            )}
                                        </select>
                                        {errors.session_id && <p className="text-red-600 text-sm mt-2">{errors.session_id}</p>}
                                        <p className="text-gray-500 text-xs mt-2">Select which WhatsApp device to send messages from</p>
                                    </div>

                                    {/* Audience Selection */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Recipients *
                                        </label>
                                        
                                        {/* Tab Navigation */}
                                        <div className="border-b border-gray-200 mb-4">
                                            <nav className="flex -mb-px">
                                                <button
                                                    type="button"
                                                    onClick={() => setData('input_method', 'manual')}
                                                    className={`py-3 px-4 text-center border-b-2 font-medium text-sm ${
                                                        data.input_method === 'manual'
                                                            ? 'border-indigo-500 text-indigo-600'
                                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                                    }`}
                                                >
                                                    Manual Entry
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setData('input_method', 'file')}
                                                    className={`py-3 px-4 text-center border-b-2 font-medium text-sm ${
                                                        data.input_method === 'file'
                                                            ? 'border-indigo-500 text-indigo-600'
                                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                                    }`}
                                                >
                                                    Upload File
                                                </button>
                                            </nav>
                                        </div>

                                        {data.input_method === 'manual' ? (
                                            <div>
                                                <textarea 
                                                    id="phone_numbers" 
                                                    value={data.phone_numbers} 
                                                    onChange={(e) => setData('phone_numbers', e.target.value)} 
                                                    className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                                                    rows="6" 
                                                    placeholder="2348012345678&#10;2349012345679&#10;2347012345678"
                                                ></textarea>
                                                {errors.phone_numbers && <p className="text-red-600 text-sm mt-2">{errors.phone_numbers}</p>}
                                                <p className="text-gray-500 text-xs mt-2">
                                                    Enter phone numbers in international format without + sign (one per line). 
                                                    {recipientCount > 0 && <span className="font-medium text-indigo-600"> {recipientCount} recipients</span>}
                                                </p>
                                            </div>
                                        ) : (
                                            <div>
                                                <div className="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors">
                                                    <input 
                                                        type="file" 
                                                        id="contacts_file" 
                                                        onChange={(e) => setData('contacts_file', e.target.files[0])} 
                                                        className="hidden" 
                                                    />
                                                    <label htmlFor="contacts_file" className="cursor-pointer">
                                                        <svg className="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <p className="text-gray-600 mb-2">Click to upload or drag and drop</p>
                                                        <p className="text-gray-400 text-sm">CSV, XLS, XLSX files (max 10MB)</p>
                                                        {data.contacts_file && (
                                                            <p className="text-indigo-600 text-sm mt-2 font-medium">
                                                                Selected: {data.contacts_file.name}
                                                            </p>
                                                        )}
                                                    </label>
                                                </div>
                                                {progress && (
                                                    <div className="mt-4">
                                                        <div className="w-full bg-gray-200 rounded-full h-2">
                                                            <div 
                                                                className="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                                                                style={{ width: `${progress.percentage}%` }}
                                                            ></div>
                                                        </div>
                                                        <p className="text-sm text-gray-600 mt-2">Uploading: {progress.percentage}%</p>
                                                    </div>
                                                )}
                                                {errors.contacts_file && <p className="text-red-600 text-sm mt-2">{errors.contacts_file}</p>}
                                            </div>
                                        )}
                                    </div>

                                    {/* Message Composition with Emoji Picker */}
                                    <div>
                                        <div className="flex items-center justify-between mb-2">
                                            <label htmlFor="message" className="block text-sm font-medium text-gray-700">
                                                Message Content *
                                            </label>
                                            <button
                                                type="button"
                                                onClick={() => setShowEmojiPicker(!showEmojiPicker)}
                                                className="flex items-center space-x-1 px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                            >
                                                <span>😀</span>
                                                <span>Add Emoji</span>
                                            </button>
                                        </div>

                                        {/* Emoji Picker */}
                                        {showEmojiPicker && (
                                            <div className="mb-4 border border-gray-200 rounded-xl bg-white shadow-lg">
                                                <div className="p-4 max-h-48 overflow-y-auto">
                                                    {emojiCategories.map((category, index) => (
                                                        <div key={index} className="mb-4 last:mb-0">
                                                            <h4 className="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">
                                                                {category.name}
                                                            </h4>
                                                            <div className="grid grid-cols-8 gap-1">
                                                                {category.emojis.map((emoji, emojiIndex) => (
                                                                    <button
                                                                        key={emojiIndex}
                                                                        type="button"
                                                                        onClick={() => insertEmoji(emoji)}
                                                                        className="text-lg hover:bg-gray-100 rounded-lg p-2 transition-colors"
                                                                    >
                                                                        {emoji}
                                                                    </button>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                                <div className="border-t border-gray-200 px-4 py-2 bg-gray-50">
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowEmojiPicker(false)}
                                                        className="text-xs text-gray-500 hover:text-gray-700"
                                                    >
                                                        Close
                                                    </button>
                                                </div>
                                            </div>
                                        )}

                                        <textarea 
                                            ref={textareaRef}
                                            id="message" 
                                            value={data.message} 
                                            onChange={(e) => setData('message', e.target.value)} 
                                            className="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" 
                                            rows="8" 
                                            placeholder="Hello! This is a broadcast message from our company..."
                                        ></textarea>
                                        {errors.message && <p className="text-red-600 text-sm mt-2">{errors.message}</p>}
                                        <div className="flex justify-between items-center mt-2">
                                            <p className="text-gray-500 text-xs">Write your message in a friendly, engaging tone</p>
                                            <p className="text-gray-400 text-xs">{data.message.length} characters</p>
                                        </div>
                                    </div>

                                    {/* Media Upload with Preview */}
                                    <div>
                                        <label htmlFor="media_file" className="block text-sm font-medium text-gray-700 mb-2">
                                            Media Attachment (Optional)
                                        </label>
                                        
                                        {mediaPreview ? (
                                            <div className="border border-gray-300 rounded-xl p-4">
                                                <div className="flex items-center justify-between mb-3">
                                                    <span className="text-sm font-medium text-gray-700">Media Preview</span>
                                                    <button
                                                        type="button"
                                                        onClick={removeMedia}
                                                        className="text-red-600 hover:text-red-800 text-sm font-medium"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                                
                                                {mediaPreview.type.startsWith('image/') ? (
                                                    <div className="flex items-center space-x-4">
                                                        <img 
                                                            src={mediaPreview.url} 
                                                            alt="Preview" 
                                                            className="w-20 h-20 object-cover rounded-lg"
                                                        />
                                                        <div className="flex-1">
                                                            <p className="text-sm text-gray-600">{mediaPreview.name}</p>
                                                            <p className="text-xs text-gray-400">Image file</p>
                                                        </div>
                                                    </div>
                                                ) : mediaPreview.type.startsWith('video/') ? (
                                                    <div className="flex items-center space-x-4">
                                                        <div className="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                                                            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                        <div className="flex-1">
                                                            <p className="text-sm text-gray-600">{mediaPreview.name}</p>
                                                            <p className="text-xs text-gray-400">Video file</p>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="flex items-center space-x-4">
                                                        <div className="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                                                            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </div>
                                                        <div className="flex-1">
                                                            <p className="text-sm text-gray-600">{mediaPreview.name}</p>
                                                            <p className="text-xs text-gray-400">Document file</p>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ) : (
                                            <div className="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-indigo-400 transition-colors">
                                                <input 
                                                    ref={fileInputRef}
                                                    type="file" 
                                                    id="media_file" 
                                                    onChange={handleMediaChange}
                                                    className="hidden" 
                                                    accept="image/*,video/*,.pdf,.doc,.docx"
                                                />
                                                <label htmlFor="media_file" className="cursor-pointer flex items-center space-x-4">
                                                    <div className="p-3 bg-indigo-100 rounded-lg">
                                                        <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div className="flex-1">
                                                        <p className="text-gray-600 mb-1">Click to upload media file</p>
                                                        <p className="text-gray-400 text-sm">Images, videos, PDF, documents (max 16MB)</p>
                                                    </div>
                                                </label>
                                            </div>
                                        )}
                                        {errors.media_file && <p className="text-red-600 text-sm mt-2">{errors.media_file}</p>}
                                    </div>

                                    {/* Campaign Summary */}
                                    <div className="bg-blue-50 p-6 rounded-xl border border-blue-200">
                                        <h4 className="font-semibold text-blue-900 mb-4 text-lg">Campaign Summary</h4>
                                        <div className="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span className="text-blue-700">Recipients:</span>
                                                <span className="font-semibold text-blue-900 ml-2">{recipientCount}</span>
                                            </div>
                                            <div>
                                                <span className="text-blue-700">Device:</span>
                                                <span className="font-semibold text-blue-900 ml-2">
                                                    {devices.find(d => d.session_id === data.session_id)?.name || 'Not selected'}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-blue-700">Message Length:</span>
                                                <span className="font-semibold text-blue-900 ml-2">{data.message.length} chars</span>
                                            </div>
                                            <div>
                                                <span className="text-blue-700">Media:</span>
                                                <span className="font-semibold text-blue-900 ml-2">
                                                    {data.media_file ? 'Yes' : 'No'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Submit Button */}
                                    <div className="flex justify-end pt-6 border-t border-gray-200">
                                        <button
                                            type="submit"
                                            className="px-8 py-4 bg-green-600 text-white font-medium rounded-xl shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 flex items-center space-x-3"
                                            disabled={processing || devices.length === 0}
                                        >
                                            {processing ? (
                                                <>
                                                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                                    <span>Starting Campaign...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>
                                                    <span>Launch Campaign 🚀</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {/* Recent Campaigns Sidebar */}
                        <div className="lg:col-span-1">
                            <div className="bg-white rounded-2xl shadow-lg p-6 sticky top-6">
                                <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center space-x-2">
                                    <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                    <span>Recent Campaigns</span>
                                </h3>
                                
                                {recentCampaigns && recentCampaigns.length > 0 ? (
                                    recentCampaigns.map((campaign) => {
                                        const stats = getCampaignStats(campaign);
                                        const status = getStatusBadge(campaign);
                                        return (
                                            <div key={campaign.id} className="border border-gray-200 rounded-lg p-4 mb-3 hover:shadow-md transition-shadow">
                                                <div className="flex justify-between items-start mb-2">
                                                    <span className={`text-xs font-medium px-2 py-1 rounded-full ${status.class}`}>
                                                        {status.text}
                                                    </span>
                                                    <span className="text-xs text-gray-500">
                                                        {new Date(campaign.queued_at).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                <p className="text-sm text-gray-600 mb-2 line-clamp-2">
                                                    {campaign.message.length > 60 ? campaign.message.substring(0, 60) + '...' : campaign.message}
                                                </p>
                                                <div className="flex justify-between text-xs text-gray-500">
                                                    <span>Sent: {campaign.sent_count}/{campaign.total_recipients}</span>
                                                    <span>{stats.successRate}%</span>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-6 text-gray-500">
                                        <svg className="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h10a2 2 0 012 2v2M7 7a2 2 0 012-2h6a2 2 0 012 2v2"></path>
                                        </svg>
                                        <p className="text-sm">No campaigns yet</p>
                                        <p className="text-xs mt-1">Create your first campaign to get started</p>
                                    </div>
                                )}
                                
                                {recentCampaigns && recentCampaigns.length > 0 && (
                                    <Link
                                        href={route('campaigns.index')}
                                        className="w-full mt-4 px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors text-center block"
                                    >
                                        View All Campaigns →
                                    </Link>
                                )}
                            </div>

                            {/* Quick Tips */}
                            <div className="bg-yellow-50 rounded-2xl shadow-lg p-6 mt-6 border border-yellow-200">
                                <h3 className="text-lg font-bold text-yellow-900 mb-3 flex items-center space-x-2">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Best Practices</span>
                                </h3>
                                <ul className="space-y-2 text-sm text-yellow-800">
                                    <li className="flex items-start space-x-2">
                                        <span className="text-yellow-600 mt-0.5">•</span>
                                        <span>Use emojis to make messages more engaging</span>
                                    </li>
                                    <li className="flex items-start space-x-2">
                                        <span className="text-yellow-600 mt-0.5">•</span>
                                        <span>Keep messages personal and relevant</span>
                                    </li>
                                    <li className="flex items-start space-x-2">
                                        <span className="text-yellow-600 mt-0.5">•</span>
                                        <span>Test with small groups first</span>
                                    </li>
                                    <li className="flex items-start space-x-2">
                                        <span className="text-yellow-600 mt-0.5">•</span>
                                        <span>Include clear call-to-action</span>
                                    </li>
                                    <li className="flex items-start space-x-2">
                                        <span className="text-yellow-600 mt-0.5">•</span>
                                        <span>Media files help increase engagement</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}