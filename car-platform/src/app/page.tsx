'use client'

import { useState, useEffect } from 'react'
import Navbar from '@/components/Navbar'
import CarCard from '@/components/CarCard'
import { Search, Filter } from 'lucide-react'

interface Car {
  id: string
  brand: string
  model: string
  year: number
  color: string
  mileage: number
  fuel: string
  transmission: string
  price: number
  type: string
  description?: string
  available: boolean
  agency: {
    id: string
    name: string
    phone: string
    email: string
    address: string
  }
}

export default function Home() {
  const [cars, setCars] = useState<Car[]>([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [filters, setFilters] = useState({
    type: '',
    fuel: '',
    minPrice: '',
    maxPrice: ''
  })
  const [showFilters, setShowFilters] = useState(false)

  useEffect(() => {
    fetchCars()
  }, [filters, searchTerm])

  const fetchCars = async () => {
    try {
      const params = new URLSearchParams()
      
      if (searchTerm) params.append('search', searchTerm)
      if (filters.type) params.append('type', filters.type)
      if (filters.fuel) params.append('fuel', filters.fuel)
      if (filters.minPrice) params.append('minPrice', filters.minPrice)
      if (filters.maxPrice) params.append('maxPrice', filters.maxPrice)

      const response = await fetch(`/api/cars?${params.toString()}`)
      const data = await response.json()
      setCars(data)
    } catch (error) {
      console.error('Erreur lors du chargement des voitures:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value }))
  }

  const clearFilters = () => {
    setFilters({
      type: '',
      fuel: '',
      minPrice: '',
      maxPrice: ''
    })
    setSearchTerm('')
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navbar />
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Hero Section */}
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Trouvez votre voiture idéale
          </h1>
          <p className="text-xl text-gray-600 mb-8">
            Découvrez des milliers de voitures à vendre et à louer par des agences de confiance
          </p>
        </div>

        {/* Search and Filters */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-8">
          <div className="flex flex-col md:flex-row gap-4 mb-4">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-5 w-5" />
              <input
                type="text"
                placeholder="Rechercher une marque, modèle..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              <Filter className="h-5 w-5" />
              <span>Filtres</span>
            </button>
          </div>

          {showFilters && (
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t">
              <select
                value={filters.type}
                onChange={(e) => handleFilterChange('type', e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Tous types</option>
                <option value="VENTE">À vendre</option>
                <option value="LOCATION">À louer</option>
              </select>

              <select
                value={filters.fuel}
                onChange={(e) => handleFilterChange('fuel', e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Tous carburants</option>
                <option value="ESSENCE">Essence</option>
                <option value="DIESEL">Diesel</option>
                <option value="HYBRIDE">Hybride</option>
                <option value="ELECTRIQUE">Électrique</option>
                <option value="GPL">GPL</option>
              </select>

              <input
                type="number"
                placeholder="Prix min"
                value={filters.minPrice}
                onChange={(e) => handleFilterChange('minPrice', e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />

              <input
                type="number"
                placeholder="Prix max"
                value={filters.maxPrice}
                onChange={(e) => handleFilterChange('maxPrice', e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />

              <button
                onClick={clearFilters}
                className="md:col-span-4 px-4 py-2 text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                Effacer les filtres
              </button>
            </div>
          )}
        </div>

        {/* Cars Grid */}
        {loading ? (
          <div className="text-center py-12">
            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p className="mt-2 text-gray-600">Chargement des voitures...</p>
          </div>
        ) : cars.length > 0 ? (
          <>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold text-gray-900">
                {cars.length} voiture{cars.length > 1 ? 's' : ''} trouvée{cars.length > 1 ? 's' : ''}
              </h2>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {cars.map((car) => (
                <CarCard key={car.id} car={car} />
              ))}
            </div>
          </>
        ) : (
          <div className="text-center py-12">
            <p className="text-xl text-gray-600 mb-4">Aucune voiture trouvée</p>
            <p className="text-gray-500">Essayez de modifier vos critères de recherche</p>
          </div>
        )}
      </div>
    </div>
  )
}
