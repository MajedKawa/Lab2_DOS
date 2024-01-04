<?php

namespace App\Http\Controllers;

class CatalogController extends Controller
{
    public function index()
    {
        $data = [];
        if (($handle = fopen(storage_path('app/catalog.csv'), 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $data[] = [
                    'id' => $row[0],
                    'title' => $row[1],
                    'quantity' => $row[2],
                    'price' => $row[3],
                    'topic' => $row[4],
                ];
            }
            fclose($handle);
        }

        return response()->json(['catalog' => $data]);
    }

    public function show($id)
    {
        // Ensure the item number is a valid integer
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid item number.'], 400);
        }

        // Initialize a variable to store the found book
        $foundBook = null;

        if (($handle = fopen(storage_path('app/catalog.csv'), 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($row[0] == $id) {
                    $foundBook = [
                        'id' => $row[0],
                        'title' => $row[1],
                        'quantity' => $row[2],
                        'price' => $row[3],
                        'topic' => $row[4],
                    ];
                    break;
                }
            }
            fclose($handle);

            if ($foundBook) {
                return response()->json(['book' => $foundBook]);
            } else {
                return response()->json(['error' => 'Book not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Failed to open the catalog file.'], 500);
        }
    }
    public function search($topic)
    {
        $matchingBooks = null;

        $topic = $this->getDecodedTopic($topic);

        $topic = trim($topic);

        if (($handle = fopen(storage_path('app/catalog.csv'), 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (trim($row[4]) == $topic) {
                    $matchingBooks[] = [
                        'id' => $row[0],
                        'title' => $row[1],
                        'quantity' => $row[2],
                        'price' => $row[3],
//                        'topic' => $row[4],
                    ];
                }
            }
        }
        fclose($handle);

        if(!empty($matchingBooks)) {
            // If matching books are found, return their information
            return response()->json(['Here are the matching books' => $matchingBooks], 200);

        } else {
            // If no matching books are found, return a message
            return response()->json(['message' => 'No books found with the specified topic.'], 404);
        }

    }

    public function update($id) {

        $rows = [];

        // Open CSV for reading
        if(($handle = fopen(storage_path('app/catalog.csv'), 'r')) !== false) {

            // Read rows
            while(($row = fgetcsv($handle)) !== false) {

                // Check if this is the item being updated
                if($row[0] == $id) {

                    // Get existing quantity
                    $quantity = $row[2];

                    // Decrement quantity
                    $quantity--;

                    // Update row with decremented quantity
                    $row[2] = $quantity;

                }

                // Add row to output array
                $rows[] = $row;

            }

            // Close file
            fclose($handle);

        } else {

            return response()->json(['error' => 'Failed to open file'], 500);

        }

        // Open CSV for writing
        if(($handle = fopen(storage_path('app/catalog.csv'), 'w')) !== false) {

            // Write updated rows
            foreach($rows as $row) {
                fputcsv($handle, $row);
            }

            // Flush changes to the file
            fflush($handle);

            // Close file
            fclose($handle);

        }

        return response()->json(['message' => 'Quantity decremented']);

    }

    private function getDecodedTopic($topic)
    {
        return urldecode($topic);
    }

}
