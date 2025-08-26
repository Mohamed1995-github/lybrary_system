import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { getTokenFromRequest, verifyToken } from '@/lib/auth'

// GET /api/cars - Lister toutes les voitures avec filtres
export async function GET(request: NextRequest) {
  try {
    const { searchParams } = new URL(request.url)
    const brand = searchParams.get('brand')
    const fuel = searchParams.get('fuel')
    const type = searchParams.get('type')
    const minPrice = searchParams.get('minPrice')
    const maxPrice = searchParams.get('maxPrice')
    const search = searchParams.get('search')

    const where: any = {
      available: true
    }

    if (brand) {
      where.brand = { contains: brand, mode: 'insensitive' }
    }

    if (fuel) {
      where.fuel = fuel
    }

    if (type) {
      where.type = type
    }

    if (minPrice) {
      where.price = { ...where.price, gte: parseFloat(minPrice) }
    }

    if (maxPrice) {
      where.price = { ...where.price, lte: parseFloat(maxPrice) }
    }

    if (search) {
      where.OR = [
        { brand: { contains: search, mode: 'insensitive' } },
        { model: { contains: search, mode: 'insensitive' } },
        { description: { contains: search, mode: 'insensitive' } }
      ]
    }

    const cars = await prisma.car.findMany({
      where,
      include: {
        agency: {
          select: {
            id: true,
            name: true,
            phone: true,
            email: true,
            address: true
          }
        }
      },
      orderBy: {
        createdAt: 'desc'
      }
    })

    return NextResponse.json(cars)

  } catch (error) {
    console.error('Erreur lors de la récupération des voitures:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}

// POST /api/cars - Créer une nouvelle voiture (agences seulement)
export async function POST(request: NextRequest) {
  try {
    const token = getTokenFromRequest(request)
    if (!token) {
      return NextResponse.json(
        { error: 'Token d\'authentification manquant' },
        { status: 401 }
      )
    }

    const payload = verifyToken(token)
    if (!payload || payload.role !== 'AGENCY') {
      return NextResponse.json(
        { error: 'Accès non autorisé' },
        { status: 403 }
      )
    }

    const body = await request.json()
    const {
      brand,
      model,
      year,
      color,
      mileage,
      fuel,
      transmission,
      price,
      type,
      description,
      images
    } = body

    // Vérifier que l'utilisateur a une agence
    const user = await prisma.user.findUnique({
      where: { id: payload.userId },
      include: { agency: true }
    })

    if (!user?.agency) {
      return NextResponse.json(
        { error: 'Aucune agence associée à cet utilisateur' },
        { status: 400 }
      )
    }

    const car = await prisma.car.create({
      data: {
        brand,
        model,
        year: parseInt(year),
        color,
        mileage: parseInt(mileage),
        fuel,
        transmission,
        price: parseFloat(price),
        type,
        description,
        images: JSON.stringify(images || []),
        agencyId: user.agency.id
      },
      include: {
        agency: {
          select: {
            id: true,
            name: true,
            phone: true,
            email: true,
            address: true
          }
        }
      }
    })

    return NextResponse.json(car, { status: 201 })

  } catch (error) {
    console.error('Erreur lors de la création de la voiture:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}