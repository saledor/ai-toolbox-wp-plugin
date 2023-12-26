=== AI ToolBox ===
Contributors: Mustafa Öztürk
Tags: AI, content, editing, WordPress, OpenAI
Requires at least: 4.0
Tested up to: 6.4.2
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI ToolBox enhances your WordPress experience by leveraging AI for content generation, editing, and product text suggestions.

== Description ==

AI ToolBox is a WordPress plugin designed to improve your content management workflow. It uses OpenAI's ChatGPT for various tasks such as content generation, editing, and even SEO.

Features:
- Content Directive: Get AI-generated articles based on your input.
- Focused SEO Questions & Keywords: Get SEO-friendly content.
- Instant Suggestions: Receive immediate content and editing tips from the AI.

Note: ChatGPT API key is needed to utilize these features.

== Installation ==

1. Upload `ai-toolbox` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the settings page to enter your ChatGPT API key.

== Frequently Asked Questions ==

Q: Is it free?
A: Yes, the plugin is free to use. However, you will need a ChatGPT API key which may have its own cost.

== Changelog ==

= 1.0.2 =
* Validate API key while in settings page.

= 1.0.1 =
* Updated readme to include detailed information about the use of OpenAI's third-party service.
* Corrected the Stable Tag in the readme to match the plugin's version number.
* Updated 'Tested Up To' to the latest WordPress version.
* Replaced remotely hosted Bootstrap CSS and JS with local copies.
* Sanitized, validated, and escaped all data inputs and outputs as per WordPress standards.
* Updated all function, class, and namespace names to use a unique and distinct prefix to avoid conflicts.
* Added direct file access prevention to all PHP files.

= 1.0.0 =
* Initial release.

## Third-Party Services

This plugin uses OpenAI's API to provide AI Generated texts. 

### OpenAI API Integration
- **Service Provider:** OpenAI
- **Service Description:** This plugin integrates with OpenAI's API to enhance content creation and editing capabilities. OpenAI offers advanced AI-driven text generation and modification services. When users submit text through our plugin, it utilizes OpenAI's powerful language models to generate new content or modify existing content, thereby streamlining the content creation process and offering creative, AI-powered suggestions.
- **When It's Used:** The OpenAI API is called upon when a user submits text via our plugin with the intent to generate new content or edit existing content. This could be for tasks such as drafting blog posts, editing articles, or creating engaging content for web pages. The text submitted by the user, along with certain system directives, is forwarded to OpenAI's API, which then processes the input and returns AI-generated or edited content based on the input provided.
- **OpenAI Website:** [https://openai.com/](https://openai.com/)
- **OpenAI API Documentation:** [https://platform.openai.com/docs/api-reference](https://platform.openai.com/docs/api-reference)
- **Terms of Use:** [https://openai.com/policies/terms-of-use](https://openai.com/policies/terms-of-use)
- **Privacy Policy:** [https://openai.com/policies/privacy-policy](https://openai.com/policies/privacy-policy)
