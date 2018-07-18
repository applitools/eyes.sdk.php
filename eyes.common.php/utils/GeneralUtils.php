<?php

namespace Applitools;

/**
 * General purpose utilities.
 */
abstract class GeneralUtils
{
    const DATE_FORMAT_ISO8601 = "yyyy-MM-dd'T'HH:mm:ss'Z'";
    const DATE_FORMAT_RFC1123 = "E, dd MMM yyyy HH:mm:ss 'GMT'";

    /**
     * @param inputStream The stream which content we would like to read.
     * @return The entire contents of the input stream as a string.
     * @throws java.io.IOException If there was a problem reading/writing
     * from/to the streams used during the operation.
     */

    public static function readToEnd(InputStream $inputStream)/* throws IOException */
    {
        ArgumentGuard::notNull(inputStream, "inputStream");

        //noinspection SpellCheckingInspection
        /*ByteArrayOutputStream baos = new ByteArrayOutputStream();
        byte[] buffer = new byte[1024];
        int length;
        while ((length = inputStream.read(buffer)) != -1) {
        baos.write(buffer, 0, length);
        }

        return new String(baos.toByteArray());*/
        echo "MOCK_MOCK"; //FIXME
        die();
    }

     /**
     * Sleeps the input amount of microseconds.
     *
     * @param int $microseconds The number of microsecondsto sleep.
     */
    public static function sleep($microseconds)
    {
        try {
            usleep((int)$microseconds);
        } catch (\Exception $ex) {
            throw new \RuntimeException("sleep interrupted", $ex);
        }
    }

    /**
     *
     * @param int $start The start time. (microseconds)
     * @param int $end The end time. (microseconds).
     * @return int The elapsed time between the start and end times, rounded up
     * to a full second, in microseconds.
     */
    public static function getFullSecondsElapsedTimeMicroseconds($start, $end)
    {
         return (ceil(($end - $start) / 1000000.0)) * 1000000;
    }

    /**
     * Creates a {@link String} from a file specified by {@code resource}.
     *
     * @param resource The resource path.
     * @return string The resource's text.
     */
    public static function readTextFromResource($resource)
    {
        /* InputStre   am is = GeneralUtils.class.getClassLoader()
         .getResourceAsStream(resource);

         BufferedReader br = new BufferedReader(new InputStreamReader(is));
         StringBuilder sb = new StringBuilder();
         try {
         String line = br.readLine();
         while (line != null) {
         sb.append(line);
         sb.append(System.lineSeparator());
         line = br.readLine();
         }

         try {
         br.close();
         } catch (IOException e) {
         // Nothing to do.
         }
         } catch (IOException e) {
         try {
         br.close();
         } catch (IOException e2) {
         // Nothing to do.
         }
         throw new EyesException("Failed to read text from resource: ", e);
         }
         return sb.toString();*/
        echo "MOCK5_MOCK5"; //FIXME
        die();
    }
}

?>