"""Extract text from PDF (including many secured Dice exports). Usage: python extract_pdf_text.py <path>"""
import sys

def main() -> int:
    if len(sys.argv) < 2:
        print("Usage: extract_pdf_text.py <pdf_path>", file=sys.stderr)
        return 1

    path = sys.argv[1]

    try:
        from pypdf import PdfReader
    except ImportError:
        print("pypdf is not installed. Run: pip install pypdf", file=sys.stderr)
        return 2

    try:
        reader = PdfReader(path)
        parts = []
        for page in reader.pages:
            parts.append(page.extract_text() or "")
        text = "\n".join(parts).strip()
        if len(text) < 80:
            print("Very little text extracted from PDF.", file=sys.stderr)
            return 3
        sys.stdout.buffer.write(text.encode("utf-8", errors="replace"))
        return 0
    except Exception as e:
        print(str(e), file=sys.stderr)
        return 4


if __name__ == "__main__":
    raise SystemExit(main())
