import React, { useState, useEffect } from 'react';
import { apiClient } from '@/lib/api-client';
import { Loader2, FileText, Smartphone, ExternalLink } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface PDFViewerProps {
  lessonId: string;
}

export default function PDFViewer({ lessonId }: PDFViewerProps) {
  const [pdfUrl, setPdfUrl] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [iframeError, setIframeError] = useState(false);
  const [isMobile, setIsMobile] = useState(false);
  const [isCapacitor, setIsCapacitor] = useState(false);

  // Detect mobile device and Capacitor
  useEffect(() => {
    const checkMobile = () => {
      const userAgent = navigator.userAgent.toLowerCase();
      const isMobileDevice = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent);
      const isSmallScreen = window.innerWidth < 768;
      
      // Only consider it mobile if it's actually a mobile device, not just small screen
      const isMobile = isMobileDevice;
      
      console.log('Mobile detection:', {
        userAgent: userAgent,
        isMobileDevice: isMobileDevice,
        isSmallScreen: isSmallScreen,
        windowWidth: window.innerWidth,
        finalIsMobile: isMobile
      });
      
      setIsMobile(isMobile);
    };

    const checkCapacitor = () => {
      // Check if running in Capacitor
      const isCapacitorApp = window.Capacitor && window.Capacitor.isNative;
      setIsCapacitor(isCapacitorApp);
      console.log('Capacitor detection:', { isCapacitorApp });
    };

    checkMobile();
    checkCapacitor();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  useEffect(() => {
    const fetchPDF = async () => {
      try {
        setLoading(true);
        setError(null);
        setIframeError(false);
        
        console.log('=== PDF DEBUG START ===');
        console.log('Lesson ID:', lessonId);
        console.log('Is Mobile:', isMobile);
        console.log('API URL:', `${import.meta.env.VITE_API_URL}/lessons/${lessonId}/pdf`);
        
        const response = await apiClient.get(`/lessons/${lessonId}/pdf`, {
          responseType: 'blob',
          headers: {
            'Accept': 'application/pdf'
          }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        console.log('Blob size:', response.data.size);
        console.log('Blob type:', response.data.type);
        
        // Check if blob is actually a PDF
        if (response.data.type !== 'application/pdf') {
          console.error('Response is not a PDF! Type:', response.data.type);
          setError('Server returned non-PDF content');
          return;
        }
        
        // Create blob URL from the PDF data
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        
        console.log('Created blob URL:', url);
        console.log('Blob URL type:', blob.type);
        console.log('Blob URL size:', blob.size);
        
        setPdfUrl(url);
        
        // Test if the blob URL is accessible
        const testResponse = await fetch(url);
        console.log('Blob URL test response:', testResponse.status, testResponse.type);
        
        console.log('=== PDF DEBUG END ===');
        
      } catch (err: any) {
        console.error('=== PDF ERROR ===');
        console.error('Error details:', err);
        console.error('Error response:', err.response);
        console.error('Error message:', err.message);
        setError(err.response?.data?.message || err.message || 'Failed to load PDF');
      } finally {
        setLoading(false);
      }
    };

    fetchPDF();

    // Cleanup blob URL on unmount
    return () => {
      if (pdfUrl) {
        console.log('Cleaning up blob URL:', pdfUrl);
        URL.revokeObjectURL(pdfUrl);
      }
    };
  }, [lessonId, isMobile]);

  const handleIframeError = () => {
    console.log('=== IFRAME ERROR ===');
    console.log('Iframe failed to load, showing fallback');
    setIframeError(true);
  };

  const handleIframeLoad = () => {
    console.log('=== IFRAME SUCCESS ===');
    console.log('Iframe loaded successfully');
  };

  const openInNewTab = () => {
    console.log('Opening PDF in new tab:', `${import.meta.env.VITE_API_URL}/lessons/${lessonId}/pdf/public`);
    window.open(`${import.meta.env.VITE_API_URL}/lessons/${lessonId}/pdf/public`, '_blank');
  };

  // Capacitor-specific functions
  const openInNativeBrowser = async () => {
    try {
      if (isCapacitor && window.Capacitor?.Plugins?.Browser) {
        const { Browser } = await import('@capacitor/browser');
        await Browser.open({
          url: `${import.meta.env.VITE_API_URL}/lessons/${lessonId}/pdf/public`,
          presentationStyle: 'popover'
        });
      } else {
        openInNewTab();
      }
    } catch (error) {
      console.error('Error opening in native browser:', error);
      openInNewTab();
    }
  };

  const downloadPDF = async () => {
    try {
      if (isCapacitor && window.Capacitor?.Plugins?.Filesystem) {
        const { Filesystem, Directory } = await import('@capacitor/filesystem');
        
        // Download the PDF
        const response = await fetch(`${import.meta.env.VITE_API_URL}/lessons/${lessonId}/pdf/public`);
        const blob = await response.blob();
        const arrayBuffer = await blob.arrayBuffer();
        const base64 = btoa(String.fromCharCode(...new Uint8Array(arrayBuffer)));
        
        // Save to Downloads folder
        const fileName = `lesson-${lessonId}.pdf`;
        await Filesystem.writeFile({
          path: `Download/${fileName}`,
          data: base64,
          directory: Directory.ExternalStorage,
          recursive: true
        });
        
        console.log('PDF downloaded successfully to Downloads folder');
      } else {
        // Fallback for web
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = `lesson-${lessonId}.pdf`;
        link.click();
      }
    } catch (error) {
      console.error('Error downloading PDF:', error);
      // Fallback to web download
      const link = document.createElement('a');
      link.href = pdfUrl;
      link.download = `lesson-${lessonId}.pdf`;
      link.click();
    }
  };

  // Remove the timeout - it's causing false positives
  // useEffect(() => {
  //   if (pdfUrl) {
  //     const timeout = setTimeout(() => {
  //       console.log('=== IFRAME TIMEOUT ===');
  //       console.log('Iframe taking too long to load, showing fallback');
  //       setIframeError(true);
  //     }, 10000); // 10 second timeout

  //     return () => clearTimeout(timeout);
  //   }
  // }, [pdfUrl]);

  if (loading) {
    return (
      <div className="w-full h-[60vh] min-h-[400px] md:h-[70vh] md:min-h-[500px] bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin text-blue-600 mx-auto mb-4" />
          <p className="text-sm text-gray-600">Loading PDF...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="w-full h-[60vh] min-h-[400px] md:h-[70vh] md:min-h-[500px] bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <p className="text-sm text-red-600 mb-2">{error}</p>
          <p className="text-xs text-gray-500 mb-4">Please try opening in a new tab</p>
          <Button onClick={openInNewTab} size="sm">
            Open in New Tab
          </Button>
        </div>
      </div>
    );
  }

  if (!pdfUrl) {
    return (
      <div className="w-full h-[60vh] min-h-[400px] md:h-[70vh] md:min-h-[500px] bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <p className="text-sm text-gray-600">PDF not available</p>
        </div>
      </div>
    );
  }

  // Mobile-specific interface
  if (isMobile && window.innerWidth < 768) {
    return (
      <div className="w-full h-[60vh] min-h-[400px] bg-gray-100 flex items-center justify-center">
        <div className="text-center p-6 max-w-sm mx-auto">
          <FileText className="h-16 w-16 text-blue-600 mx-auto mb-4" />
          <h3 className="font-semibold mb-2">PDF Document</h3>
          <p className="text-sm text-gray-600 mb-4">
            {isCapacitor 
              ? "Open the PDF in your browser or download it to your device."
              : "Download the PDF to read it on your device. Return here when you're done to mark the lesson as complete."
            }
          </p>
          <div className="space-y-3">
            {isCapacitor && (
              <Button 
                onClick={openInNativeBrowser}
                className="w-full"
                size="lg"
              >
                <ExternalLink className="h-4 w-4 mr-2" />
                Open in Browser
              </Button>
            )}
            <Button 
              onClick={downloadPDF}
              variant={isCapacitor ? "outline" : "default"}
              className="w-full"
              size="lg"
            >
              {isCapacitor ? "Download to Device" : "Download PDF"}
            </Button>
          </div>
        </div>
      </div>
    );
  }

  // If iframe failed to load, show fallback
  if (iframeError) {
    return (
      <div className="w-full h-[60vh] min-h-[400px] md:h-[70vh] md:min-h-[500px] bg-gray-100 flex items-center justify-center">
        <div className="text-center p-6">
          <FileText className="h-16 w-16 text-blue-600 mx-auto mb-4" />
          <h3 className="font-semibold mb-2">PDF Document</h3>
          <p className="text-sm text-gray-600 mb-4">
            Unable to display PDF in this view. Please open it in a new tab for the best experience.
          </p>
          <Button onClick={openInNewTab} className="w-full">
            Open PDF in New Tab
          </Button>
        </div>
      </div>
    );
  }

  // Desktop iframe viewer
  return (
    <div className="relative">
      <iframe
        src={`${pdfUrl}#toolbar=1&navpanes=0&scrollbar=1&statusbar=0&messages=0&view=FitH`}
        className="w-full h-[60vh] min-h-[400px] md:h-[70vh] md:min-h-[500px]"
        title="PDF Viewer"
        frameBorder="0"
        onError={handleIframeError}
        onLoad={handleIframeLoad}
      />
      {/* Fallback button for mobile if iframe doesn't work */}
      <div className="absolute top-2 right-2 md:hidden">
        <Button 
          variant="outline" 
          size="sm" 
          onClick={openInNewTab}
          className="bg-white/90 backdrop-blur-sm"
        >
          Open
        </Button>
      </div>
    </div>
  );
}
