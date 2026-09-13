import asyncio
from playwright.async_api import async_playwright
import os

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page()
        
        # Determine the absolute path to the HTML file
        html_file = os.path.abspath("Project_Summary.html")
        file_url = f"file:///{html_file.replace(os.sep, '/')}"
        
        await page.goto(file_url)
        # Give it a brief moment to render fonts
        await page.wait_for_timeout(1000)
        
        pdf_path = os.path.abspath("Project_Summary.pdf")
        await page.pdf(path=pdf_path, format="A4", print_background=True, margin={"top": "20px", "bottom": "20px", "left": "20px", "right": "20px"})
        print(f"PDF successfully generated at: {pdf_path}")
        
        await browser.close()

if __name__ == "__main__":
    asyncio.run(run())
