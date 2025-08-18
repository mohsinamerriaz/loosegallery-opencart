<div align="center">
    <img src="https://www.loosegallery.com/wp-content/uploads/2025/05/logo.png" alt="LooseGallery">
</div>

## Contents

- [About LooseGallery](#about-loosegallery)
- [Installation & Setup](#Installation-and-setup)

## About LooseGallery

LooseGallery is a creative platform where everyday people can become artists, personalize their art, and express themselves without limits; it also serves as a respectful space for passionate artists to showcase their work and a marketplace that connects creators with art enthusiasts and consumers.

## Installation & Setup

1. Clone this repository and extract the contents, ZIP the contents of the folder to `loosegallery.ocmod.zip`.
2. Log in to your OpenCart admin panel.
3. Go to **Extensions → Extension Installer** and upload the `loosegallery.ocmod.zip` file.
4. Navigate to **Extensions → Modifications** and click the **Refresh** button to update the cache.
5. Go to **Extensions → Modules**, find **LooseGallery**, and click **Edit**.
6. Enter the following configuration values:
   - **API Key** – Sign Up with LooseGallery and contact the Technical Support at tech@loosegallery.com to get your unique key.
   - **LooseGallery to Website Redirect URL** – Enter your cart’s address (e.g., `https://yourstore.com/index.php?route=checkout/cart`).
   - **Website to LooseGallery Redirect URL** – Enter the editor URL with your specific Editor ID:
     ```
     https://editor.loosegallery.com/editor/?dom=YOUR_EDITOR_ID
     ```
   - **Product** – Select the OpenCart product you want to connect to the editor.
   - **LooseGallery Terms and Conditions** – Add the legal notice or terms to show at checkout (e.g., copyright notice for templates). This will appear with a mandatory checkbox.

7. Click **Save** to apply the settings.

---