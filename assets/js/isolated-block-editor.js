(function(window, $) {
    const wp = window.wp;

    // Safety check for WP dependencies at the very top
    if ( ! wp || ! wp.blockEditor || ! wp.element || ! wp.components || ! wp.blocks || ! wp.data ) {
        console.warn('Cotlas Travel Desk: WP Block Editor dependencies not found. Falling back to plain textareas.');
        $(document).ready(function() {
            $('.ctd-isolated-block-editor').show();
        });
        return;
    }

    const { element, blockEditor, components, blocks, data, compose } = wp;
    const { useState, useEffect, createElement: h, Fragment, render, unmountComponentAtNode } = element;
    const { BlockEditorProvider, BlockList, BlockTools, WritingFlow, ObserveTyping } = blockEditor;
    const { SlotFillProvider, Popover } = components;
    const { parse, serialize } = blocks;

    // Allowed blocks configuration
    const ALLOWED_BLOCKS = [ 
        'core/paragraph', 
        'core/heading', 
        'core/list', 
        'core/image', 
        'core/quote', 
        'core/table', 
        'core/html', 
        'core/spacer' 
    ];

    function IsolatedEditor({ initialContent, onChange }) {
        // Initialize blocks from HTML
        const [ blocks, setBlocks ] = useState( () => parse( initialContent ) );

        // Sync changes back to HTML
        useEffect( () => {
            onChange( serialize( blocks ) );
        }, [ blocks ] );

        const settings = {
            allowedBlockTypes: ALLOWED_BLOCKS,
            mediaUpload: ( { filesList, onFileChange } ) => {
                 // Basic stub for media upload
            },
            // hasFixedToolbar: false, // Default to floating toolbar
            // Disable features that might cause conflicts or are unnecessary
            __experimentalBlockPatterns: [],
            __experimentalReuseBlocks: false,
        };

        // Important: SlotFillProvider must wrap BlockEditorProvider to ensure toolbars work correctly
        // without interfering with the main editor's slots if possible.
        return h( SlotFillProvider, {},
            h( BlockEditorProvider, {
                value: blocks,
                onInput: setBlocks,
                onChange: setBlocks,
                settings: settings
            },
                h( 'div', { className: 'ctd-isolated-editor-styles editor-styles-wrapper' },
                    h( BlockTools, {},
                        h( WritingFlow, {},
                            h( ObserveTyping, {},
                                h( BlockList, {} )
                            )
                        )
                    ),
                    // Popover.Slot ensures tooltips/popovers render within this provider's context
                    h( Popover.Slot )
                )
            )
        );
    }

    // Error Boundary to prevent crashing the whole page
    class ErrorBoundary extends element.Component {
        constructor(props) {
            super(props);
            this.state = { hasError: false };
        }

        static getDerivedStateFromError(error) {
            return { hasError: true };
        }

        componentDidCatch(error, errorInfo) {
            console.error("Isolated Block Editor Error:", error, errorInfo);
        }

        render() {
            if (this.state.hasError) {
                return h('div', { className: 'ctd-editor-error' }, 'Editor error. Switching to plain text.');
            }
            return this.props.children;
        }
    }

    function mountEditor( textarea ) {
        const $textarea = $(textarea);
        if ($textarea.data('editor-mounted')) return;
        
        let id = $textarea.attr('id');
        if (!id) {
             id = 'editor-' + Math.random().toString(36).substr(2, 9);
             $textarea.attr('id', id);
        }

        $textarea.hide();

        const $container = $('<div class="ctd-isolated-editor-container"></div>').insertAfter($textarea);
        
        try {
            // Remove RegistryProvider wrapper to use the global registry (fixes registerShortcut error)
            // But keep ErrorBoundary to catch conflicts
            render(
                h( ErrorBoundary, {},
                    h( IsolatedEditor, {
                        initialContent: $textarea.val(),
                        onChange: ( content ) => {
                            $textarea.val( content );
                            $textarea.trigger('change');
                        }
                    } )
                ),
                $container[0]
            );
            $textarea.data('editor-mounted', true);
            $textarea.data('editor-container', $container[0]);
        } catch (e) {
            console.error("Failed to mount editor:", e);
            $textarea.show();
            $container.remove();
        }
    }
    
    function unmountEditor( textarea ) {
        const $textarea = $(textarea);
        const container = $textarea.data('editor-container');
        if (container) {
            unmountComponentAtNode(container);
            $(container).remove();
            $textarea.show();
            $textarea.data('editor-mounted', false);
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Init on load
        $('.ctd-isolated-block-editor').each(function() {
            mountEditor(this);
        });

        // Handle Repeater Add
        $(document).on('click', '.ctd-repeater-add, .ctd-add-sub-repeater', function() {
             setTimeout(() => {
                 $('.ctd-isolated-block-editor').each(function() {
                     mountEditor(this);
                 });
             }, 100);
        });
        
        // Handle Repeater Remove (Cleanup)
        $(document).on('click', '.ctd-repeater-remove', function() {
            const $item = $(this).closest('.ctd-repeater-item');
            $item.find('.ctd-isolated-block-editor').each(function() {
                unmountEditor(this);
            });
        });

        // Handle Sort Start/Stop (jQuery UI Sortable)
        if ($.fn.sortable) {
            $('.ctd-sortable-list').on('sortstart', function(event, ui) {
                 ui.item.find('.ctd-isolated-block-editor').each(function() {
                     unmountEditor(this);
                 });
            });
            
            $('.ctd-sortable-list').on('sortstop', function(event, ui) {
                 ui.item.find('.ctd-isolated-block-editor').each(function() {
                     mountEditor(this);
                 });
            });
        }
    });

})(window, jQuery);
